<?php

namespace App\Http\Controllers;


use App\Models\Area;
use Illuminate\Http\Request;
use App\Models\DEOS_controller;
use App\Models\Building;
use App\Models\DEOS_point;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Traits\AssemblinInit;
use App\Imports\PointsImport;

use stdClass;

class DEOS_controllerController extends Controller
{
    use AssemblinInit;
    public function index()
    {
        $buildings = Building::all();
        $controllers = DEOS_controller::all();
        foreach ($controllers as $item) {
            $item->building;
            $item->points;
        }

        return view('admin.controller.index', compact('controllers', 'buildings'));
    }

    public function checkControllerValid( Request $request)
    {
        $samebuildingcontrollers = DEOS_controller::where('building_id', $request->building_id)->get();
        foreach($samebuildingcontrollers as $controller) {
            if ( $controller-> name == $request->name) return false;
        }
        return true;
    }
    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'ip_address' => 'required',
            'port_number' => 'required',
            'building_id' => 'required'
        ],[
            'name.required' => "Name field can't be empty",
            'ip_address.required' => "IP Address can't be empty",
            'port_number.required' => "Port Number can't be empty",
            'building_id.required' => 'Controller must be belonged to a Building'
        ]);

        if ( !$this->checkControllerValid ( $request )) {
            $building = Building::where('id', $request->building_id) -> first();
            return back()->with('error', sprintf("The Building  \"%s\" already have a controller named %s.", $building->name, $request->name));
        }
        
        $controllers = DEOS_controller::all();
        $request->port_number = count($controllers) + 8001;
        $row = DEOS_controller::create([
            'name' => $request->name,
            'ip_address' => $request->ip_address,
            'port_number' => count($controllers) + 8001,
            'building_id' => $request->building_id
        ]);
        $this->updateConfigfiles();

        return back()->with('success', 'Created successfully');
    }
    public function show($id)
    {
        $controller = DEOS_controller::where('id', $id)->first();
        $buildings = Building::all();
        $points = $controller->points;
        $controllers = DEOS_controller::all();
        $areas = Area::all();
        return view('admin.controller.details', compact('controller', 'buildings', 'controllers', 'areas'));
    }

    public function update(Request $request, $id)
    {
                
        $controller = DEOS_controller::where('id', $id)->first();
        if (!$controller) {
            return back()->with('error', 'Not found');
        }

        $this->validate($request, [
            'name' => 'required',
            'ip_address' => 'required',
            'port_number' => 'required',
            'building_id' => 'required'
        ],[
            'name.required' => "Name field can't be empty",
            'ip_address.required' => "IP Address can't be empty",
            'port_number.required' => "Port Number can't be empty",
            'building_id.required' => 'Controller must be belonged to a Building'
        ]);
        

        if ($controller->name != $request->name &&  !$this->checkControllerValid ( $request )) {
            $building = Building::where('id', $request->building_id) -> first();
            return back()->with('error', sprintf("The Building  \"%s\" already have a controller named %s.", $building->name, $request->name));
        }

        $controller->update($request->all());

        $this->updateConfigfiles();
        return back()->with('success', 'Updated successfully');
    }

    public function destroy($id)
    {
        $controller = DEOS_controller::where('id', $id)->first();
        if (!$controller) {
            return back()->with('error', 'Not found');
        }

        $filepath = config()->get('constants.BASE_CONFIG_PATH') . 'asmrest/' . $controller->name . ".json";
        if (file_exists($filepath)) unlink($filepath);
        $controller->delete();

        $this->updateConfigfiles();
        return redirect()->route('controllers')->with('success', 'Deleted successfully');
    }

    public function createPoint(Request $request, $id)
    {
        $this->validate($request, ['name' => 'required', 'label' => 'required']);

        DEOS_point::create(['name' => $request->name, 'label' => $request->label, 'controller_id' => $id]);

        return back()->with('success', 'Created successfully');
    }

    public function deletePoints(Request $request, $id)
    {
        $items = [];
        foreach (json_decode($request->pointSelected) as $key => $val) {
            if ($val != true) continue;
            $items[] = $key;
        }
        DEOS_point::where('controller_id', $id)->whereIn('id', $items)->delete();

        return back()->with('success', 'Deleted successfully');
    }

    public function importPointsFromCsv(Request $request, $id)
    {
        if (!$request->file('file')) {
            return back()->with('error', 'Empty file');
        }
        $rows = Excel::toCollection(new PointsImport, $request->file('file'));       


        foreach ($rows[0] as $index => $row) {
            //If Header continue;
            if ($index == 0) continue;
            $data = [
                'name' => $row[1],
                'label' => $row[2],
                'type' => $row[4],
                'meta_property' => $row[5],
                'meta_room' => $row[6],
                'meta_sensor' => $row[7], 
                'meta_type' => $row[8],
                'value' => $row[3], 
                'controller_id' => $id,

            ];
            $point = DEOS_point::where('name', $data['name'])->first();

            if ( $point == null) {
                $point = DEOS_point::create($data);
            } else {
                $point->update($data);
            }
        }
        $this->updateConfigfiles();
        return back()->with('success', 'Imported successfully');
    }

    public function exportPointsFromCsv(Request $request, $id)
    {
        $points = DEOS_point::where('controller_id', $id)->get();
        $result = $points->map(function ($item) {
            return [$item->controller->name, $item->name, $item->label, $item->value,  $item->type, $item->meta_property, $item->meta_room, $item->meta_sensor, $item->meta_type];
        });
        $result->prepend(['Controller Name', 'Point Name', 'Point Label', 'Value', 'Type','meta_property', 'meta_room', 'meta_sensor', 'meta_type']);

        $controller_name = $points[0]->controller->name;
        return $result->downloadExcel($controller_name . '-' .time() . '.csv');
    }
}
