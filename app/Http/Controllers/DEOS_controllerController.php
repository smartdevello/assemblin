<?php

namespace App\Http\Controllers;

use App\Imports\DEOS_pointImport;
use App\Imports\DeosPointImport;
use Illuminate\Http\Request;
use App\Models\DEOS_controller;
use App\Models\Building;
use App\Models\DEOS_point;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

class DEOS_controllerController extends Controller
{
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

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:deos_controllers,name',
            'ip_address' => 'required',
            'port_number' => 'required',
        ],[
            'name.required' => "Name field can't be empty",
            'name.unique' => $request->name . ' already exists in DB.' . ' Name field should be unique.',
            'ip_address.required' => "IP Address can't be empty",
            'port_number.required' => "Port Number can't be empty",
        ]);

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

    public function updateConfigfiles()
    {

        $filepath = config()->get('constants.BASE_CONFIG_PATH') . 'asmserver/config.json';
        $content = file_get_contents($filepath);
        $content = json_decode($content);

        $controllers = DEOS_controller::all();

        $content->Slaves = [];
        foreach($controllers as $controller ){
            $item = new stdClass();
            $item->Name = $controller->name;
            $item->IP = 'localhost';
            $item->Port = $controller->port_number;
            array_push($content->Slaves, $item);
        }
        file_put_contents($filepath, json_encode($content));

        foreach($controllers as $controller) {
            $filepath = config()->get('constants.BASE_CONFIG_PATH') . 'asmrest/' . $controller->name . ".json";
            $restconfig = new stdClass();
            $restconfig->Address = '127.0.0.1';
            $restconfig->Port = $controller->port_number;
            $restconfig->Live = true;
            $restconfig->Trend = true;
            $restconfig->OpenEMS = new stdClass();
            $restconfig->OpenEMS->IP = $controller->ip_address;
            $restconfig->LP = new stdClass();
            $restconfig->LP->CheckRights = false;
            $restconfig->LP->Readable = [];
            $restconfig->LP->Writeable = [];

            $points = $controller->points;
            foreach ($points as $point ) {
                $item = new stdClass();
                $item->Label = $point->label ?? '';
                $item->Description = $point->name ?? '';
                $item->Meta = new stdClass();
                $item->Meta->property = $point->meta_property ?? '';
                $item->Meta->room = $point->meta_room ?? '';
                $item->Meta->sensor = $point->meta_sensor ?? '';
                $item->Meta->type = $point->meta_type ?? '';
                $item->Type = $point->type ?? '';
                array_push($restconfig->LP->Writeable, $item);
                array_push($restconfig->LP->Readable, $item);
            }
            file_put_contents($filepath, json_encode($restconfig));
        }

    }
    public function show($id)
    {
        $controller = DEOS_controller::where('id', $id)->first();
        $buildings = Building::all();
        $points = $controller->points;

        return view('admin.controller.details', compact('controller', 'buildings'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'ip_address' => 'required',
            'port_number' => 'required',
        ],[
            'name.required' => "Name field can't be empty",
            'ip_address.required' => "IP Address can't be empty",
            'port_number.required' => "Port Number can't be empty",
        ]);
        

        $result = DEOS_controller::where('id', $id)->first();
        if (!$result) {
            return back()->with('error', 'Not found');
        }
        $result->update($request->all());

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
        $rows = Excel::toCollection(new DeosPointImport, $request->file('file'));
        foreach ($rows[0] as $row) {
            DEOS_point::create(['name' => $row[0], 'value' => $row[1], 'controller_id' => $id]);
        }
        return back()->with('success', 'Imported successfully');
    }

    public function exportPointsFromCsv(Request $request, $id)
    {
        $points = DEOS_point::where('controller_id', $id)->get();
        $result = $points->map(function ($item) {
            return [$item->name, $item->value, $item->controller->name];
        });
        $result->prepend(['Name', 'Value', 'Controller Name']);

        return $result->downloadExcel('points.xlsx');
    }
}
