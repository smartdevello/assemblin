<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\DEOS_controller;
use App\Models\DEOS_point;
use Illuminate\Http\Request;
use App\Models\Sensor;
use App\Http\Traits\AssemblinInit;

class DashboardController extends Controller
{
    use AssemblinInit;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        //
        $sensors = $this->getSensors();

        $this->getSERVERConfig();
        $controllers = DEOS_controller::all();
        foreach($controllers as $controller)
        {
            $this->getRESTconfig($controller);
        }
        $points = DEOS_point::all();
        $areas = Area::all();
        return view('admin.dashboard', compact('sensors', 'points', 'controllers', 'areas'));
    }

    public function setting_index(){
        return view('admin.setting');
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
}
