<?php

namespace App\Http\Controllers;

use App\Models\TrendGroup;
use Illuminate\Http\Request;

class TrendGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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
            'update_interval' => 'required',
            'query_period' => 'required',
        ], [
            'controller_id.required' => "Controller ID field can't be empty",
            'trend_group_name.required' => "Trend group name can't be empty",
            'location_name.required' => "Location name can't be empty",
            'update_interval.required' => "Must specify update interval",
            'query_period.required' => "Must specify query period"

        ]);
      
        
        TrendGroup::create([
            'controller_id' => $request->controller_id,
            'trend_group_name' => $request->trend_group_name,
            'location_name' =>  $request->location_name,
            'update_interval' => $request->update_interval,
            'query_period' => $request->query_period,
            
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
}
