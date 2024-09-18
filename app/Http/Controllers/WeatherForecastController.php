<?php

namespace App\Http\Controllers;

use App\Http\Traits\WeatherForcastTrait;
use App\Models\DEOS_controller;
use App\Models\HKA_Scheduled_JOb;
use App\Models\DEOS_point;
use App\Http\Traits\AssemblinInit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class WeatherForecastController extends Controller
{

    use WeatherForcastTrait;
    use AssemblinInit;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        date_default_timezone_set('Europe/Helsinki');
        $forecast_data = [];
        $all_jobs = HKA_Scheduled_JOb::all();
        foreach ($all_jobs as $job) {
            if ($job->job_name == 'weather_forecast') {
                $controller = DEOS_controller::where('id', $job->job_id)->first();
                if ($controller && isset($controller->longitude) && isset($controller->latitude)) {
                    $json_data = $this->getWeatherData($controller->longitude, $controller->latitude);
                    foreach($json_data as $key => $data) {
                        foreach($data as $index => $item) {


                            $time = strtotime($item['time']);
                            $value = $item['value'];
                    
                            $found_key = array_search($time , array_column($forecast_data, 'time'));
                            if ($found_key !== false) {
                                $forecast_data[$found_key][$key] = $value;
                            } else {
                                
                                $forecast_data[] = [                
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
        return view('admin.weather_forecast.index', compact('forecast_data'));
    }
    public function sendToDEOS()
    {

        Log::info('sendToDEOS method hit.');
        Log::info('Authenticated User: ' . auth()->user());

        try {
            $job = HKA_Scheduled_JOb::where('job_name', 'weather_forecast')->first();
            Log::info('Here is the job: ' . $job?->id);
            $controller = DEOS_controller::where('id', $job->id)->first();
            if (!$controller) {
                Log::info('Controller not found' . $job->id);
                return back()->with('error', 'Not found');
            } else {
                Log::info('Controller found: ' . $controller->id);
            }
    
            $res = $this->sendForcasttoDEOS('weather_forecast', $controller->id);
            return response()->json($res, 200);
        } catch(Exception $e) {
            Log::error('Error in sendToDEOS: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while sending forecast to DEOS.');
        }

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
