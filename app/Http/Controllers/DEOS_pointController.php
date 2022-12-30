<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Building;
use App\Models\DEOS_controller;
use App\Models\DEOS_point;
use App\Models\Location;
use Illuminate\Http\Request;
use phpseclib3\Net\SSH2;
use stdClass;

class DEOS_pointController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        //
        $points = DEOS_point::where('meta_type', '!=', 'weather_forcast')->get();
        foreach ($points as $point) {
            $point->controller;
            $point->area;
        }

        $controllers = DEOS_controller::all();
        foreach ($controllers as $controller) {
            $controller->building;
        }

        $areas = Area::all();
        foreach ($areas as $area) {
            $area->building;
        }

        return view('admin.point.index', compact('points', 'controllers', 'areas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //

        $this->validate($request, [
            'label' => 'required',
            'controller_id' => 'required',
            'area_id' => 'required',
        ], [
            'label.required' => "Description field can't be empty",
            'controller_id.required' => "Must specify a Controller",
            'area_id.required' => "Must specify a Area",
        ]);

        try {
            $controller = DEOS_controller::where('id', $request->controller_id)->first();
            $building = Building::where('id', $controller->building_id)->first();
            $location = Location::where('id', $building->location_id)->first();
            $request->name = $location->name . "_" . $building->name . "_" . $request->name;

            $this->validate($request, [
                'name' => 'required|unique:deos_points,name',
            ], [
                'name.required' => "Name field can't be empty",
                'name.unique' => sprintf("The Point \"%s\" already exists", $request->name),
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Building or Location is not allocated for this point');
        }

        // if (! $this->checkValidPoint_forController ($request )) {
        //     $controller = DEOS_controller::where('id', $request->controller_id)->first();
        //     return back()->with('error', sprintf("The Controller \"%s\" already has the point named \"%s\"", $controller->name , $request->name));
        // }

        // if (! $this->checkValidPoint_forArea ($request )) {
        //     $area = Area::where('id', $request->area_id)->first();
        //     return back()->with('error', sprintf("The Area \"%s\" already has the point named \"%s\"", $area->name , $request->name));
        // }

        DEOS_point::create([
            'name' => $request->name,
            'label' => $request->label,
            'type' => 'FL',
            'controller_id' => $request->controller_id,
            'area_id' => $request->area_id,

        ]);
        $this->stopAsmServices();
        $this->updateConfigfiles();
        $this->startAsmServices();
        return back()->with('success', 'Created successfully');
    }
    public function checkValidPoint_forController(Request $request)
    {
        $samecontrollerPoints = DEOS_point::where('controller_id', $request->controller_id)->get();
        foreach ($samecontrollerPoints as $point) {
            if ($point->name == $request->name) {
                return false;
            }

        }
        return true;
    }
    public function checkValidPoint_forArea(Request $request)
    {
        $samecontrollerPoints = DEOS_point::where('area_id', $request->area_id)->get();
        foreach ($samecontrollerPoints as $point) {
            if ($point->name == $request->name) {
                return false;
            }

        }
        return true;
    }

    public function update(Request $request, $id)
    {

        //
        $point = DEOS_point::where('id', $id)->first();

        if (!$point) {
            return back()->with('error', 'Not found');
        }
        $point->controller;
        $point->area;

        $this->validate($request, [
            'label' => 'required',
            'controller_id' => 'required',
            'area_id' => 'required',
        ], [
            'label.required' => "Description field can't be empty",
            'controller_id.required' => "Must specify a Controller",
            'area_id.required' => "Must specify a Area",
        ]);

        try {
            $controller = DEOS_controller::where('id', $point->controller_id)->first();
            $building = Building::where('id', $controller->building_id)->first();
            $location = Location::where('id', $building->location_id)->first();
            $request->name = $location->name . "_" . $building->name . "_" . $request->name;

            $this->validate($request, [
                'name' => 'required|unique:deos_points,name',
            ], [
                'name.required' => "Name field can't be empty",
                'name.unique' => sprintf("The Point \"%s\" already exists", $request->name),
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Building or Location is not allocated for this point');
        }

        $point->update($request->all());

        $this->stopAsmServices();
        $this->updateConfigfiles();
        $this->startAsmServices();
        return back()->with('success', 'Updated Successfully');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    public function show(Request $request, $id)
    {
        //
        $point = DEOS_point::where('id', $id)->first();
        $controllers = DEOS_controller::all();
        foreach ($controllers as $controller) {
            $controller->building;
        }

        $areas = Area::all();
        foreach ($areas as $area) {
            $area->building;
        }

        return view('admin.point.details', compact('point', 'controllers', 'areas'));
    }

    public function edit($id)
    {
        //
    }

    public function restartAsmServices()
    {
        $ssh = new SSH2('172.21.8.245', 22);

        $ssh->login('Hkaapiuser', 'ApiUserHKA34!');

        // Stop services:
        echo $ssh->exec("taskkill /IM asmserver.exe /f");
        echo $ssh->exec("taskkill /IM asmrest.exe /f");
        echo $ssh->exec("schtasks /end /tn \"AsmRestService starter\"");

        //Start Services:
        echo $ssh->exec("schtasks /run /tn \"AsmRestService starter\"");

    }
    public function stopAsmServices()
    {
        $response = "";
        try {
            $ssh = new SSH2('172.21.8.245', 22);

            $ssh->login('Hkaapiuser', 'ApiUserHKA34!');

            // Stop services:
            $response = $response . $ssh->exec("taskkill /IM asmserver.exe /f");
            $response = $response . $ssh->exec("taskkill /IM asmrest.exe /f");
            $response = $response . $ssh->exec("schtasks /end /tn \"AsmRestService starter\"");

            return response()->json([
                'success' => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'response' => $response,
            ], 403);
        }
    }
    public function startAsmServices()
    {
        $response = "";
        try {
            $ssh = new SSH2('172.21.8.245', 22);

            $ssh->login('Hkaapiuser', 'ApiUserHKA34!');

            //Start Services:
            $response = $response . $ssh->exec("schtasks /run /tn \"AsmRestService starter\"");
            return response()->json([
                'success' => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'response' => $response,
            ], 403);
        }

    }
    public function updateConfigfiles()
    {

        $controllers = DEOS_controller::all();

        foreach ($controllers as $controller) {
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
            foreach ($points as $point) {
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
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $row = DEOS_point::where('id', $id)->first();
        if (!$row) {
            return back()->with('error', 'Not found');
        }
        $row->delete();
        $this->stopAsmServices();
        $this->updateConfigfiles();
        $this->startAsmServices();
        return redirect()->route('points')->with('success', 'Deleted successfully');

    }
}