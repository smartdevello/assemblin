<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\DEOS_controller;
use App\Models\DEOS_point;
use App\Models\Location;

use Illuminate\Http\Request;
use App\Models\Sensor;
use App\Models\SensorLog;
use App\Http\Traits\AssemblinInit;
use Exception;

class DashboardController extends Controller
{
    use AssemblinInit;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        //
        $sensors = $this->getSensors();
        // $sensors = Sensor::paginate(10);
        foreach($sensors as $sensor) {
            $point = $sensor->point;
            if ($point) {
                if ($point->controller_id) {
                    $controller = DEOS_controller::where('id', $point->controller_id)->first();
                    $sensor->controller_id = $controller->id;
                }
                if ( $point->area_id) {
                    $area = Area::where('id', $point->area_id)->first();
                    $sensor->area_id = $area->id;
                }
            }
            // $logdata = $sensor->getlogs();
            $sensor->logs;
            // if (isset($logdata->logs)){
            //     $sensor->logs = json_encode($logdata->logs);
            // } else $sensor->logs = null;


        }
        $this->getSERVERConfig();
        $controllers = DEOS_controller::all();
        foreach($controllers as $controller)
        {
            $this->getRESTconfig($controller);
        }

        $points = DEOS_point::all();
        foreach ($points as $point) {
            $point->controller;
            $point->area;
        }
        $areas = Area::all();
        return view('admin.dashboard', [
            'sensors' => $sensors,
            'points' => $points,
            'controllers' => $controllers,
            'areas' => $areas
        ]);
    }
    public function kiona_endpoints_index()
    {
        $sensors = $this->getSensors();
        // $sensors = Sensor::paginate(10);
        foreach($sensors as $sensor) {
            $point = $sensor->point;
            if ($point) {
                if ($point->controller_id) {
                    $controller = DEOS_controller::where('id', $point->controller_id)->first();
                    $sensor->controller_id = $controller->id;
                }
                if ( $point->area_id) {
                    $area = Area::where('id', $point->area_id)->first();
                    $sensor->area_id = $area->id;
                }
            }
            // $logdata = $sensor->getlogs();
            $sensor->logs;

        }
        $this->getSERVERConfig();

        return view('admin.kionaEndpoints.index', [
            'sensors' => $sensors
        ]);
    }
    public function get_kiona_values(){
        $this->getSensors();
        $sensors = Sensor::where('to_kiona', 1)->get();
        $return = [];
        foreach($sensors as $sensor) {
            $return[] = [
                'id' => $sensor->id,
                'deviceId' => $sensor->deviceId,
                'tag' => $sensor->tag,
                'name' => $sensor->name,
                'type' => $sensor->type,
                'unit' => $sensor->unit,
                'value' => $sensor->value,
                'created_at' => $sensor->created_at,
                'updated_at' => $sensor->updated_at,
            ]; 
        }
        return $return;

    }
    public function get_kiona_valuesByLocationName($locationName) {
        $location = Location::where('name', $locationName)->first();
        if (!$location) {
            return [];
        } else {
            $return = [];
            $buildings = $location->buildings;

            if (!$buildings) {
                return [];
            }
            foreach($buildings as $building) {
                $controllers = $building->controllers;
                if (!$controllers) break;
                foreach($controllers as $controller) {
                    $points = $controller->points;
                    if (!$points) break;
                    foreach($points as $point) {
                        $sensors = Sensor::where('point_id', $point->id)->get();
                        if (!$sensors) break;
                        foreach($sensors as $sensor) {
                            $return[] = [
                                'id' => $sensor->id,
                                'deviceId' => $sensor->deviceId,
                                'tag' => $sensor->tag,
                                'name' => $sensor->name,
                                'type' => $sensor->type,
                                'unit' => $sensor->unit,
                                'value' => $sensor->value,
                                'created_at' => $sensor->created_at,
                                'updated_at' => $sensor->updated_at,
                            ]; 
                        }

                    }
                
                }
            }
            return $return;
        }

        

    }
    public function setting_index(){
        return view('admin.setting');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
        $res = "";
        try{
            $points_updated = false;
            $asm_points_data = [];
            foreach($request->all() as $item)
            {
                $sensor = Sensor::where('id', $item['id'])->first();
                $sensor->update([
                    "value" => $item["value"],
                    "name" => $item["name"],
                    "point_id" => $item["point_id"] ?? null,
                    "visibility" => $item["visibility"]
                ]);

                if ($item['point_id']) {
                    $points_updated = true;
                    $point = DEOS_point::where('id', $item['point_id'])->first();
                    $point->update([
                        'controller_id' => $item['controller_id'] ?? null,
                        'area_id' => $item['area_id'] ?? null
                    ]);
                    $asm_points_data[] = [
                        "id" => $item["point_name"],
                        "value" => $item["value"]
                    ];
                }
            }
            if ($points_updated) {
                $res = $this->sendDatatoASM($asm_points_data);
                $this->stopAsmServices();
                $this->updateConfigfiles();
                $this->startAsmServices();
            }
        }catch(Exception $e){
            return response()->json([
                'error' => $e->getMessage()
            ], 403);
        }
        return response()->json([
            'success' => $res
        ], 200);

    }

    public function to_kiona_update(Request $request) {
        $res = "";
        try{
            foreach($request->all() as $item)
            {
                $sensor = Sensor::where('id', $item['id'])->first();
                $sensor->update([
                    "name" => $item["name"],
                    "to_kiona" => $item["to_kiona"]
                ]);

            }

        }catch(Exception $e){
            return response()->json([
                'error' => $e->getMessage()
            ], 403);
        }
        return response()->json([
            'success' => $res
        ], 200);
    }
    public function sendDatatoASM($data){
        $ch = curl_init();
        //        return $request;
        curl_setopt_array($ch, array(
            CURLOPT_URL => $this->assemblin_api_uri . '/assemblin/points/writebyid',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Accept: application/json"
            ),
        ));


        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        $result = json_encode($result);
        return json_encode($result);
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
    }
}
