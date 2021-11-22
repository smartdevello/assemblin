<?php

namespace App\Http\Controllers;

use App\Models\TrendGroup;
use Illuminate\Http\Request;
use App\Http\Traits\TrendDataTrait;
use App\Models\HKA_Scheduled_JOb;

class TrendGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    use TrendDataTrait;
    public function index()
    {
        //
        $trend_groups = TrendGroup::all();
        return view('admin.trendgroup.index', ['trend_groups' => $trend_groups]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
        //
        // 'controller_id', 'trend_group_name', 'location_name', 'update_interval', 'query_period', 'token'
        $this->validate($request, [
            'controller_id' => 'required',
            'trend_group_name' => 'required',
            'location_name' => 'required',
            'update_interval' => 'required|integer',
            'query_period' => 'required|integer',            
        ], [
            'controller_id.required' => "Controller ID field can't be empty",
            'trend_group_name.required' => "Trend group name can't be empty",
            'location_name.required' => "Location name can't be empty",
            'update_interval.required' => "Must specify update interval",
            'update_interval.integer' => "Update interval must be integer",
            'query_period.required' => "Must specify query period",
            'query_period.integer' => "Query Period must be integer",
        ]);
      
        
        $trend_group = TrendGroup::create($request->all());
        $trend_group = TrendGroup::create([
            'controller_id' => $request->controller_id,
            'trend_group_name' => $request->trend_group_name,
            'location_name' =>  $request->location_name,
            'update_interval' => $request->update_interval,
            'query_period' => $request->query_period,
            'send_to_ftp' => $request->send_to_ftp ? 1: 0
        ]);

        $job = HKA_Scheduled_JOb::create([
            'job_name' => 'trend_group',
            'job_id' => $trend_group->id,
            'next_run' => date('Y-m-d H:i:s', time() + $trend_group->update_interval * 60)
        ]);
        return back()->with('success', 'Created successfully');
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
        $trend_group = TrendGroup::where('id', $id)->first();
        return view('admin.trendgroup.details', ['trend_group' => $trend_group]);

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

        $trend_group = TrendGroup::where('id', $id)->first();


        if (!$trend_group) {
            return response()->json([
                'error' => 'not found',
            ], 404);
        }

        $this->validate($request, [
            'controller_id' => 'required',
            'trend_group_name' => 'required',
            'location_name' => 'required',
            'update_interval' => 'required|integer',
            'query_period' => 'required|integer',
        ], [
            'controller_id.required' => "Controller ID field can't be empty",
            'trend_group_name.required' => "Trend group name can't be empty",
            'location_name.required' => "Location name can't be empty",
            'update_interval.required' => "Must specify update interval",
            'update_interval.integer' => "Update interval must be integer",
            'query_period.required' => "Must specify query period",
            'query_period.integer' => "Query Period must be integer",
        ]);
       

        $trend_group->update($request->all());
        $job = HKA_Scheduled_JOb::where([
            ['job_name', '=' , 'trend_group'],
            ['job_id', '=', $trend_group->id]
        ])->first();

        if ($job) {
            $job->update([
                'job_name' => 'trend_group',
                'job_id' => $trend_group->id,
                'next_run' => date('Y-m-d H:i:s', time() + $trend_group->update_interval * 60)
            ]);
        } else {
            $job = HKA_Scheduled_JOb::create([
                'job_name' => 'trend_group',
                'job_id' => $trend_group->id,
                'next_run' => date('Y-m-d H:i:s', time() + $trend_group->update_interval * 60)
            ]);
        }


        return response()->json([
            'success' => true
        ]);

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
        $row = TrendGroup::where('id', $id)->first();
        if (!$row) {
            return response()->json([
                'error' => 'not found',
            ], 404);
        }
        $row->delete();

        $row = HKA_Scheduled_JOb::where('job_id', $id)->first();
        if ($row){
            $row->delete();
        }
        return response()->json([
            'success' => true
        ]);
    }

    // Get csv file data and save it to csv_trend_data table
    public function receive_csv(Request $request)
    {

        $this->validate($request, [
            'controller_id' => 'required',
            'trend_group_name' => 'required',
        ], [
            'controller_id.required' => "Controller ID should be provided.",
            'trend_group_name.required' => "Trend group name should be provided.",
        ]);

        $trend_group = TrendGroup::where([
            ['controller_id', '=' , $request->controller_id],
            ['trend_group_name', '=', $request->trend_group_name]
        ])->first();

        if (!$trend_group) {
            return response()->json([
                'error' => "The Trend group does not exist on DB."
            ], 404);
        }
        $output = $this->receive_csv_save_db($trend_group);
        return response()->json($output);
    }

}
