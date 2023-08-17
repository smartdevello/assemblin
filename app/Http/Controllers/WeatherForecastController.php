<?php

namespace App\Http\Controllers;

use App\Http\Traits\WeatherForcastTrait;
use App\Models\DEOS_controller;
use App\Models\HKA_Scheduled_JOb;
use App\Models\DEOS_point;

use Illuminate\Http\Request;

class WeatherForecastController extends Controller
{

    use WeatherForcastTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        date_default_timezone_set('Europe/Helsinki');
        $return_data = [];
        $all_jobs = HKA_Scheduled_JOb::all();
        foreach ($all_jobs as $job) {
            if ($job->job_name == 'weather_forecast') {
                $controller = DEOS_controller::where('id', $job->job_id)->first();
                if ($controller && isset($controller->longitude) && isset($controller->latitude)) {
                    $forecast_data = $this->getWeatherData($controller->longitude, $controller->latitude);
                    foreach($forecast_data as $key => $data) {
                        foreach($data as $index => $item) {


                            $time = strtotime($item['time']);
                            $value = $item['value'];
                    
                            $found_key = array_search($time , array_column($return_data, 'time'));
                            if ($found_key !== false) {
                                $return_data[$found_key][$key] = $value;
                            } else {
                                
                                $return_data[] = [                
                                    'time' => $time,
                                    $key => $value
                                ];
                            }

                        }
                    }
                    break;
                }
            }
        }
        return view('admin.weather_forecast.index', compact('return_data'));
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
