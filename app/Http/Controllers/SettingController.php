<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
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
    public function setting_index(){
        $devices = DB::select("select DISTINCT `deviceId` from `sensors`");
        $types = [];
        foreach($devices as $device){
            $sql = "select DISTINCT `type` from `sensors` WHERE `deviceId` = '" . $device->deviceId ."'";
            $types[$device->deviceId] = DB::select($sql);
        }
        $user = auth('sanctum')->user();

        $all_tokens =[];
        foreach($user->tokens as $token){
            $all_tokens[] = [
                "id" => $token->id,
                "name" => $token->name,
                "plainTextToken" => $token->plainTextToken,
            ];
        }
        return view('admin.setting', [
            'devices' => $devices,
            'types' => $types,
            'all_tokens' => $all_tokens,
        ]);
    }
    public function sendIntervalto_API($DevEUI, $Payload, $FPort)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api-eu.thingpark.com/thingpark/lrc/rest/downlink?DevEUI=' . $DevEUI. '&FPort='. $FPort . '&Payload=' . $Payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    public function update_device_interval(Request $request)
    {
        $elsys_payloads = [
            '2' => '3E061F00000002FE',
            '3' => '3E061F00000003FE',
            '6' => '3E061F00000006FE',
            '12' => '3E061F0000000CFE',
            '24' => '3E061F00000018FE',
            '72' => '3E061F00000048FE'
        ];

        $IOTSU_payloads = [
            '2' => '010100',
            '3' => '010101',
            '6' => '010102',
            '12' => '010103',
            '24' => '010104',
            '72' => '010106'
        ];

        $Solidus_payloads = [
            '2' => 'A10010',
            '3' => 'A10015',
            '6' => 'A10030',
            '12' => 'A10060',
            '24' => 'A100120',
            '72' => 'A1003600'
        ];
        $this->validate($request, [
            'deviceId' => 'required',
            // 'type' => 'required',
            'interval' => 'required'
        ], [
            'deviceId.required' => "Need to select a device",
            // 'type.required' => "Must specify a sensor type",
            'interval.required' => "Must specify an interval"
        ]);

        $payload = [];
        $FPort = 0;
        if ($request->deviceId == 'A81758FFFE04EF1F') {
            // If device is Elsys
            $payload = $elsys_payloads;
            $FPort = 6;
        } else if ($request->deviceId == '47EABD48004A0044' ){
            // If device is Solidus
            $payload = $Solidus_payloads;
            $FPort = 1;
        } else if ($request->deviceId == '70B3D55680000A6D'){
            // If device is IOTSUS
            $payload = $IOTSU_payloads;
            $FPort = 1;
        } else {
            return back()->with('error', 'Unknown DeviceID');
        }
        $response = $this->sendIntervalto_API($request->deviceId, $payload[$request->interval], $FPort);
        return back()->with('success', 'Updated successfully');
    }
}
