<?php

namespace App\Http\Traits;

use App\Models\Sensor;
use App\Models\Area;
use Illuminate\Http\Request;
use App\Models\DEOS_controller;
use App\Models\DEOS_point;
use stdClass;
use phpseclib3\Net\SSH2;


trait AssemblinInit {

    public $assemblin_api_uri = 'https://172.21.8.245:8000';

    public function stopAsmServices() {
        $response = "";
        try{
            $ssh = new SSH2('172.21.8.245', 22);

            $ssh->login('Hkaapiuser', 'ApiUserHKA34!');
    
            // Stop services:         
            $response = $response .  $ssh->exec("taskkill /IM asmserver.exe /f");
            $response = $response .   $ssh->exec("taskkill /IM asmrest.exe /f");
            $response = $response .   $ssh->exec("schtasks /end /tn \"AsmRestService starter\"");  
 
            return response()->json([
                'success' => $response
            ]);
        }catch(\Exception $e){
            return response()->json([
                'error' => $e->getMessage(),
                'response' => $response
            ], 403);
        }
    }
    public function startAsmServices()
    {
        $response = "";
        try{
            $ssh = new SSH2('172.21.8.245', 22);

            $ssh->login('Hkaapiuser', 'ApiUserHKA34!');   
   
    
            //Start Services:
            $response = $response .  $ssh->exec("schtasks /run /tn \"AsmRestService starter\"");
            return response()->json([
                'success' => $response
            ]);
        }catch(\Exception $e){
            return response()->json([
                'error' => $e->getMessage(),
                'response' => $response
            ], 403);
        }

    }
    public function restartAsmServices()
    {
        $response = "";
        try{
            $ssh = new SSH2('172.21.8.245', 22);

            $ssh->login('Hkaapiuser', 'ApiUserHKA34!');
    
            // Stop services:         
            $response = $response .  $ssh->exec("taskkill /IM asmserver.exe /f");
            $response = $response .   $ssh->exec("taskkill /IM asmrest.exe /f");
            $response = $response .   $ssh->exec("schtasks /end /tn \"AsmRestService starter\"");
    
    
            //Start Services:
            $response = $response .  $ssh->exec("schtasks /run /tn \"AsmRestService starter\"");
            return response()->json([
                'success' => $response
            ]);
        }catch(\Exception $e){
            return response()->json([
                'error' => $e->getMessage(),
                'response' => $response
            ], 403);
        }
    }

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


    public function checkPoints()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->assemblin_api_uri . '/assemblin/points/byid');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 400);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);

        $res = json_decode($result, true);
        return json_encode($res);
    }
    public function getPoints()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->assemblin_api_uri . '/assemblin/points/byid');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 400);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);

        $res = json_decode($result, true);
        foreach ($res as $point) {

            $row = DEOS_point::where('name', $point['id'])->first();
            if ($row === null) {
                DEOS_point::create([
                    'name' => $point['id'],
                    'value' => $point['value']
                ]);
            } else {
                $row->update(['value' => $point['value']]);
            }
        }
        return DEOS_point::all();
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



        $filepath = config()->get('constants.BASE_CONFIG_PATH') . 'asmservice/opens.json';
        $opensconfig = new stdClass();
        $opensconfig->GetProcesses = "C:/assemblin/asmrest/getprosesses.cmd";
        $opensconfig->Slaves = [];

        foreach( $controllers as $controller) {
            $item = new stdClass();
            $item->Program = "C:/assemblin/asmrest/asmrest.exe";
            $item->Config =  "-c=c:/assemblin/asmrest/" . $controller->name . ".json";
            array_push( $opensconfig->Slaves, $item);
        }

        $item = new stdClass();
        $item->Program = "C:/assemblin/asmserver/asmserver.exe";
        $item->Config =  "-c=c:/assemblin/asmserver/config.json";
        array_push($opensconfig->Slaves, $item);
        file_put_contents($filepath, json_encode($opensconfig));
    }

    public function writePointstoLocalDB(Request $request)
    {

        foreach($request->all() as $item) {
            $row = DEOS_point::where('name', $item['name'])->first();
            if ($row === null) {
                DEOS_point::create([
                    'name' => $item['name'],
                    'value' => $item['value']
                ]);
            } else {
                $row->update(['value' => $item['value']]);
            }
        }

        return DEOS_point::all();
    }

    public function writePointsbyid(Request $request)
    {

        $ch = curl_init();
        //        return $request;
        curl_setopt_array($ch, array(
            CURLOPT_URL => $this->assemblin_api_uri . '/assemblin/points/writebyid',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => json_encode($request->all()),
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


    public function getSERVERConfig()
    {
        try{
            $filepath = config()->get('constants.BASE_CONFIG_PATH') . 'asmserver/config.json';
            $content = file_get_contents($filepath);            
            $content = json_decode($content); 

            foreach($content->Slaves as &$controller){
                $row = DEOS_controller::where('name', $controller->Name)->where('port_number', $controller->Port)->first();

                $data = [
                    'name' => $controller->Name ?? '',
                    // 'ip_address' => $controller->IP ?? '',
                    'port_number' => $controller->Port ?? ''
                ];
                if ($row === null) {
                    $row = DEOS_controller::create($data);
                } else {
                    $row->update($data);
                }
                $controller->controller_id = $row->id;
                
            }
            
            return response()->json($content);
        } catch (\Exception $e){
            return response()->json([
                'error' => $e->getMessage()
            ], 403);
        }

    }

    public function getRESTconfig(DEOS_controller $controller)
    {
        if (!empty($controller->name) ) {
            try{
                $filepath = config()->get('constants.BASE_CONFIG_PATH') . 'asmrest/' . $controller->name . ".json";
                $content = file_get_contents($filepath);            
                $content = json_decode($content); 

                $controller->update([
                    'ip_address' => $content->OpenEMS->IP
                ]);

                foreach($content->LP->Writeable as $point) {
                    
                    $row = DEOS_point::where('name', $point->Description)->first();
                    $data = [
                        'name' => $point->Description,
                        'label' => $point->Label,
                        'type' => $point->Type,
                        'meta_property' => $point->Meta->property ?? '',
                        'meta_room' => $point->Meta->room ?? '',
                        'meta_sensor' => $point->Meta->sensor ?? '',
                        'meta_type' => $point->Meta->type ?? '',
                        'controller_id' => $controller->id
                    ];
                    
                    if ($row === null)  {
                        $row = DEOS_point::create($data);
                    } else {
                        $row->update($data);
                    }
                }

                return response()->json($content);

            }catch (\Exception $e){
                return response()->json([
                    'error' => $e->getMessage()
                ], 403);
            }

        } else {
            return response()->json([
                'error' => 'Something went wrong!'
            ], 404);
        }
    }

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
}