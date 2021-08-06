<?php

namespace App\Http\Controllers;

use App\Models\DEOS_point;
use Illuminate\Http\Request;
use App\Models\Sensor;
use App\Models\DEOS_controller;
use App\Models\Area;
use App\Http\Traits\AssemblinInit;


class FoxeriotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    use AssemblinInit;
    public function index()
    {
        //
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
    public function automatic_update()
    {
        $this->getSensors();
        $sensors = Sensor::all();
        $data = [];

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
                array_push($data, array("id" => $point->name, "value" => strval($sensor->value)));
            }            
        }

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => 'https://172.21.8.245:8000/assemblin/points/writebyid',
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
            curl_close($ch);
            return response()->json([
                'error' => curl_error($ch)
            ], 403);
        }

        curl_close($ch);

        return response()->json([
            'success' => $result,
            'data' => $data
        ], 200);        
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
