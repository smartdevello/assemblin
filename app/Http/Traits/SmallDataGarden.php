<?php

namespace App\Http\Traits;

use App\Models\Sensor;
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

        curl_close($curl);
        return json_decode($response);
    }

    public function SmallDataGarden_getDeviceData($deviceId)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->api_baseurl . '/devices/' . $deviceId . '/data',
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

        foreach($devices as $device)
        {
            $deviceData = $this->SmallDataGarden_getDeviceData($device->DeviceID);
            
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
            foreach($deviceData[0] as $key => $value)
            {
                if (in_array( $key, $ignore_list)) continue;
                if (!is_numeric($value)) continue;
                $dbdata['type'] = $key;
                $dbdata['value'] = $value;

                Sensor::updateOrCreate(
                    ['deviceId' => $device->DeviceID, 'type' => $key] , $dbdata
                );  

            }

        }

        return response()->json( $devices, 200);
    }
}