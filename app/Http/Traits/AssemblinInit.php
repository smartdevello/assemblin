<?php

namespace App\Http\Traits;

use App\Models\Sensor;
use Illuminate\Http\Request;
use App\Models\DEOS_point;

trait AssemblinInit {

    public $assemblin_api_uri = 'https://172.21.8.245:8000';

    public function getSensors()
    {
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

        foreach ($res['data'] as &$device) {
            foreach ($device['latestObservations'] as &$sensor) {
               
                $data = array(
                    'deviceId' => $device['deviceId'],
                    'type' => $sensor['variable'],
                    'observationId' => $sensor['id'],
                    'tag' => implode(" ", $device['tags']),
                    'name' => $device['displayName'],
                    'type' => $sensor['variable'],
                    'unit' => $sensor['unit'] ?? '',
                    'value' => $sensor['value'],
                    'message_time' => $sensor['message-time'],                    
                );

                Sensor::updateOrCreate(
                    ['deviceId' => $device['deviceId'], 'type' => $sensor['variable']] , $data                    
                );               

            }
        }

        return Sensor::all();
    }

    public function getObservations(Request $request)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.foxeriot.com/api/v1/get-observations?deviceId=' . $request['deviceId'],
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
        $response = json_decode($response);

        return response()->json($response);
    }
    public function updateSensorsPoint(Request $request)
    {
        try {
            $res = [];
            foreach ($request->all() as $item) {
                $point = DEOS_point::where('name', $item['point_name'])->first();
                $row = Sensor::updateOrCreate(
                    ['deviceId' => $item['deviceId'], 'type' => $item['variable']],
                    array(
                        'point_id' => $point->id,
                        'point_name' => $item['point_name']                        
                    )
                );
                array_push($res, $row);
            }
            return $res;
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 403);
        }
    }
}