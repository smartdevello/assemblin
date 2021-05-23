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
        $sensors = Sensor::paginate(10);
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
            }
            
        }
        $this->getSERVERConfig();
        $controllers = DEOS_controller::all();
        foreach($controllers as $controller)
        {
            $this->getRESTconfig($controller);
        }

        $points = DEOS_point::all();
        foreach ($points as $point) {
            $point->controller;
            $point->area;
        }

        $areas = Area::all();
        return view('admin.dashboard', [
            'sensors' => $sensors,
            'points' => $points,
            'controllers' => $controllers,
            'areas' => $areas
        ]);
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
    public function update(Request $request)
    {
        //
        $points_updated = false;
        foreach($request->all() as $item)
        {
            $sensor = Sensor::where('id', $item['id'])->first();
            $sensor->update([
                "value" => $item["value"],
                "point_id" => $item["point_id"] ?? null,                
            ]);

            if ($item['point_id']) {
                $points_updated = true;
                $point = DEOS_point::where('id', $item['point_id'])->first();
                $point->update([
                    'controller_id' => $item['controller_id'] ?? null,
                    'area_id' => $item['area_id'] ?? null
                ]);
            }
        }
        if ($points_updated) {
            $this->updateConfigfiles();
        }
        return $request->all();
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
