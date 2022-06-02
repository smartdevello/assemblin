<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SmallDataGardenController extends Controller
{
    //
    public function getAlldevices()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://smalldata.fi/v2/devices',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'X-Sdg-Token: ZN1hMXTYB6kKE2Dsa7djYPFqM451e7OV'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function updateSensor()
    {
        $devices = $this->getAlldevices();
        foreach($devices as $device)
        {
            var_dump($device);
        }
    }
}
