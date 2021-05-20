<?php

namespace App\Http\Controllers;

use App\Models\DEOS_point;
use Illuminate\Http\Request;
use App\Models\Sensor;
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
    public function getDEOS_point_name(Request $request)
    {

        try {
            
            // foreach ($request->all() as $item) {
            //     $row = Sensor::where('deviceId', $request['deviceId'])->where('type', $request['variable'])->first();

            // }

            return response()->json($request->all(), 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 403);
        }
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
        $this->getDevices();
        $sensors = Sensor::all();
        $data = [];
        foreach ($sensors as $sensor) {
            if ($sensor->point_name !== null && $sensor->point_name !== "") {
                array_push($data, array("id" => $sensor->point_name, "value" => strval($sensor->value)));
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
            return curl_error($ch);
        }
        curl_close($ch);
        return $result;
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
