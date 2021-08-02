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
        return view('admin.setting', [
            'devices' => $devices,
            'types' => $types
        ]);
    }
    public function update_device_interval(Request $request)
    {
        $elsys_payloads = [
            '3E061F00000001FE', '3E061F00000002FE', '3E061F00000003FE', '3E061F00000004FE',
            '3E061F00000005FE', '3E061F00000006FE', '3E061F00000007FE', '3E061F00000008FE',
            '3E061F00000009FE', '3E061F0000000AFE', '3E061F0000000BFE', '3E061F0000000CFE'
        ];

        $this->validate($request, [
            'deviceId' => 'required',
            'type' => 'required',
            'interval' => 'required'
        ], [
            'deviceId.required' => "Need to select a device",
            'type.required' => "Must specify a sensor type",
            'interval.required' => "Must specify an interval"
        ]);

        if ($request->deviceId == 'A81758FFFE04EF1F') {
            // If device is Elsys
        } else {
            return back()->with('error', 'Building or Location is not allocated for this point');
        }        
        return back()->with('success', 'Updated Successfully');
    }
}
