<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;

class FoxeriotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }
    public function getDevices(){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.foxeriot.com/api/v1/get-devices',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOlsiaHR0cHM6Ly9iYWNrZW5kLmZveGVyaW90LmNvbS9hcGkvIl0sInN1YiI6ImF1dGgwfDYwNTlmOGNkMGY3YjJkMDA2OGI3ZDViZSIsImlzcyI6Imh0dHBzOi8vYmFja2VuZC5mb3hlcmlvdC5jb20iLCJleHAiOjQxMDIzNTg0MDAsImF6cCI6Ik9jT1E1RU1FUEJWTUVBQm5lUldYdlR4bnM1VDFzSTdzIiwic2NvcGUiOiJvcGVuaWQiLCJjZl90b2tlbl9zZXJpYWwiOjEzMywiY2Zfcm9sZSI6MTEwMTEsImNmX3Rva2VuX3Njb3BlIjoicm9sZSIsImlhdCI6MTYxNjUxMjc1OX0.RpqIrjkoWe80rmzVUpPb4UI81N45SeDT87NVWd9q0Zo'
            ),
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);

        $res = json_decode($response, true);

        foreach($res['data'] as &$device){
            $chunks = Sensor::where('deviceId', $device['deviceId']);
            foreach($device['latestObservations'] as &$sensor){
                $row = $chunks->where('type', $sensor['variable'])->first();

                if ($row === null) {
                    $row = new Sensor();
                    $row->id = $sensor['id'];
                }
                $row->deviceId = $device['deviceId'];
                $row->tag = implode(" ", $device['tags']);
                $row->name = $device['displayName'];
                $row->type = $sensor['variable'];
                $row->unit = $sensor['unit'];
                $row->value = $sensor['value'];
                $row->message_time = $sensor['message-time'];
                $row->save();
                $sensor['DEOS_pointId'] = $row->DEOS_pointId;
                return json_encode($row);
            }
        }
        return json_encode($res);

    }
    public function getObservations(Request $request){

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.foxeriot.com/api/v1/get-observations?deviceId='.$request['deviceId'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOlsiaHR0cHM6Ly9iYWNrZW5kLmZveGVyaW90LmNvbS9hcGkvIl0sInN1YiI6ImF1dGgwfDYwNTlmOGNkMGY3YjJkMDA2OGI3ZDViZSIsImlzcyI6Imh0dHBzOi8vYmFja2VuZC5mb3hlcmlvdC5jb20iLCJleHAiOjQxMDIzNTg0MDAsImF6cCI6Ik9jT1E1RU1FUEJWTUVBQm5lUldYdlR4bnM1VDFzSTdzIiwic2NvcGUiOiJvcGVuaWQiLCJjZl90b2tlbl9zZXJpYWwiOjEzMywiY2Zfcm9sZSI6MTEwMTEsImNmX3Rva2VuX3Njb3BlIjoicm9sZSIsImlhdCI6MTYxNjUxMjc1OX0.RpqIrjkoWe80rmzVUpPb4UI81N45SeDT87NVWd9q0Zo'
            ),
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        return response()->json($response);
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
    public function update(Request $request, $id)
    {
        //
        $sensor = Sensor::find($id);
        $sensor->DEOS_pointId = $request['DEOS_pointId'];
        $sensor->save();
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
