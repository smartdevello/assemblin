<?php

namespace App\Http\Traits;

use App\Models\Sensor;
use App\Models\SensorLog;
use Illuminate\Support\Facades\Log;
use stdClass;

trait SmallDataGarden{
    public $api_baseurl = 'https://smalldata.fi/v2/';
    public $api_token = 'ZN1hMXTYB6kKE2Dsa7djYPFqM451e7OV';
    public function SmallDataGarden_getAlldevices()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->api_baseurl  . 'devices',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'X-Sdg-Token: ' . $this->api_token
        ),
        ));

        $response = curl_exec($curl);
        if(curl_errno($curl)) {
            Log::error('Curl error: ' . curl_error($curl));
        }
        curl_close($curl);        
        return json_decode($response);
    }

    public function SmallDataGarden_getDeviceData($deviceId)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->api_baseurl . 'devices/' . $deviceId . '/data',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'X-Sdg-Token: ' . $this->api_token
        ),
        ));

        $response = curl_exec($curl);
        if(curl_errno($curl)) {
            Log::error('Curl error: ' . curl_error($curl));
        }
        curl_close($curl);
        return json_decode($response);
    }
    public function SmallDataGarden_updateSensors()
    {
        $devices = $this->SmallDataGarden_getAlldevices();
        $ignore_list = [
            "date",
            "id",
            "Ack",
            "Alert",
            "Average mode",
            "Data duplication",
            "Transmit interval",
            "rawData",
            "Tx interval",
            "ack",
        ];

        $res = [];
        foreach($devices as $device)
        {
            $deviceData = $this->SmallDataGarden_getDeviceData($device->DeviceID);
            $res[] = $deviceData;
            $dbdata = array(
                'deviceId' => $device->DeviceID,
                //'type' => 'battvolt',
                'observationId' => null,
                'tag' => $device->GroupName ?? '',
                'name' => $device->FriendlyName ?? '',
                'unit' => '',
                //'value' => $deviceData[0]->battvolt,
                'message_time' => $device->Time,
            );
            if ($deviceData) {
                foreach($deviceData[0] as $key => $value)
                {
                    try{
                        if (in_array( $key, $ignore_list)) continue;
                        if (!is_numeric($value)) continue;
                        $dbdata['type'] = $key;
                        $dbdata['value'] = $value;
        
                        $sensor = Sensor::updateOrCreate(
                            ['deviceId' => $device->DeviceID, 'type' => $key] , $dbdata
                        );
                        $log = SensorLog::where('sensor_id', $sensor->id)->first();
                        $log_data = array(
                            'sensor_id' => $sensor->id,
                        );
                        if ( !isset($log) ) {
                            $log_data['logs'] = json_encode([
                                date('Y-m-d H:i:s') => $sensor->value
                            ]);
                        } else {
                            $log_data['logs'] = (array)json_decode($log->logs);
                            $len = count($log_data['logs']);
                            if ( $len > 9 ){
                                $log_data['logs'] = array_slice( $log_data['logs'] ,  $len - 9);
                            }
                            $log_data['logs'][date('Y-m-d H:i:s')] = $sensor->value;
        
                            $log_data['logs'] = json_encode($log_data['logs']);
                        }
                        $log = SensorLog::updateOrCreate(
                            ['sensor_id' => $sensor->id, ] , $log_data
                        );
                    } catch (\Exception $e) {
                        continue;
                    }

    
                }
            }


        }

        return response()->json( $res, 200);
    }
}
