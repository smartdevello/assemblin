<?php

namespace App\Http\Controllers;

use App\Models\TrendGroup;
use Illuminate\Http\Request;
use App\Models\Csv_Trend_Data;
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
            'update_interval' => 'required',
            'query_period' => 'required',
        ], [
            'controller_id.required' => "Controller ID field can't be empty",
            'trend_group_name.required' => "Trend group name can't be empty",
            'location_name.required' => "Location name can't be empty",
            'update_interval.required' => "Must specify update interval",
            'query_period.required' => "Must specify query period"

        ]);
       

        $trend_group->update($request->all());
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
        return response()->json([
            'success' => true
        ]);
    }

    // Get csv file data and save it to csv_trend_data table
    public function receive_csv(Request $request)
    {
        $trend_group = TrendGroup::where([
            ['controller_id', '=' , $request->controller_id],
            ['trend_group_name', '=', $request->trend_group_name]
        ])->first();

        if (!$trend_group) {
            return response()->json([
                'error' => "The Trend group does not exist on DB."
            ], 404);
        }
        $filename = "myfile.csv";
        $format = "lynx --dump 'http://172.21.8.245/COSMOWEB?TYP=REGLER&MSG=GET_TRENDVIEW_DOWNLOAD_CVS&COMPUTERNR=THIS&REGLERSTRANG=%s&REZEPT=%s&FROMTIME=%d&TOTIME=%d&' > " . $filename ;
        $command = sprintf($format, $request->controller_id, $request->trend_group_name, $request->from_time, $request->to_time);

        if ( file_exists($filename ) ) {
            unlink($filename);
        }
        shell_exec($command);

        $file = fopen($filename,"r");
        $csv_data = [];
        $index = 0;
        while(! feof($file))
        {
            $index++;
            $row = fgetcsv($file, 0, ';');
            if (is_array($row)) 
                $csv_data[] = $row;         
        }
        fclose($file);

        $output = [];
        foreach($csv_data as $index => $arr)
        {
            if ($index !=0) {
                $timestamp = 0;

                if ( count($arr) < 3 ) continue;
                foreach($arr as $key => $value) {

                    if ($key == 1){
                        $timestamp = strtotime( trim($arr[0]). " " . trim($arr[1]));
                    } else if ($key !=0 && $key !=1) {
                        if ( empty( $csv_data[0][$key] ) || empty( $value ) ) continue;

                        $csv_trend_data = Csv_Trend_Data::create([
                            'trend_group_id' => $trend_group->id,
                            'timestamp' => date('Y-m-d H:i:s', $timestamp),
                            'sensor_name' => $csv_data[0][$key],
                            'sensor_value' => floatval ( str_replace(",", "", $value) )
                        ]);
                        if ($csv_trend_data) 
                            $output[] = $csv_trend_data;
                    }
                }
            }

        }
        return response()->json($output);
    }

}
