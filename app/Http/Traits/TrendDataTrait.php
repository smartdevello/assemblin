<?php

namespace App\Http\Traits;
use Illuminate\Http\Request;
use App\Models\Csv_Trend_Data;

trait TrendDataTrait
{

    public function receive_csv_save_db(Request $request, $trend_group)
    {
                
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

                        $csv_trend_data = Csv_Trend_Data::where([
                            ['trend_group_id', '=' , $trend_group->id],
                            ['timestamp', '=', date('Y-m-d H:i:s', $timestamp)],
                            ['sensor_name', '=', $csv_data[0][$key]],
                            ['sensor_value', '=', floatval ( str_replace(",", "", $value) ) ],
                        ])->first();

                        if (!$csv_trend_data){
                            $csv_trend_data = Csv_Trend_Data::create([
                                'trend_group_id' => $trend_group->id,
                                'timestamp' => date('Y-m-d H:i:s', $timestamp),
                                'sensor_name' => $csv_data[0][$key],
                                'sensor_value' => floatval ( str_replace(",", "", $value) )
                            ]);
                        }
                        if ($csv_trend_data)
                            $output[] = $csv_trend_data;

                    }
                }
            }
        }

        return $output;
    }
}
