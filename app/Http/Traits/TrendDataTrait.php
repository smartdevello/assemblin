<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use App\Models\Csv_Trend_Data;
use Illuminate\Support\Facades\Storage;
use DateTime;
use stdClass;

trait TrendDataTrait
{

    public function receive_csv_and_savefile_sendto_external_ftp($trend_group)
    {
        $now = date('Y_m_d_H_i_', time());
        $date = date('Y_m_d', time());
        $local_filename = str_replace(" ", "_", sprintf("%s%s%s.csv", $now, $trend_group->trend_group_name, $trend_group->controller_id));

        $local_folderpath =sprintf("storage/%s/", $date);

        if (!file_exists($local_folderpath)) {
            mkdir($local_folderpath, 0777, true);
        }

        $to_time = time();
        $from_time = $to_time - $trend_group->query_period * 60;

        //Convet it to milisecond;
        $from_time *=1000;
        $to_time *= 1000;



        $format = "lynx --dump 'http://172.21.8.245/COSMOWEB?TYP=REGLER&MSG=GET_TRENDVIEW_DOWNLOAD_CVS&COMPUTERNR=THIS&REGLERSTRANG=%s&REZEPT=%s&FROMTIME=%d&TOTIME=%d&' > " . $local_folderpath . $local_filename ;
        $command = sprintf($format, $trend_group->controller_id, $trend_group->trend_group_name, $from_time, $to_time);        
        shell_exec($command);

        $sftp = Storage::disk('sftp');
        $local_storage_path = str_replace(" ", "_", sprintf("%s/%s%s%s.csv", $date, $now, $trend_group->trend_group_name, $trend_group->controller_id));
        $remote_storage_path = sprintf("%s%s%s.csv", $now, $trend_group->trend_group_name, $trend_group->controller_id);

        $sftp->put($remote_storage_path, file_get_contents(storage_path($local_storage_path) ));

    }
    public function convertFinnishtoEnglish($finnish) {
        $patterns = [
            'ä' => 'a',
            'ö' => 'o',
            'Ä' => 'A',
            'Ö'=> 'O',
            ' ' => '_'
        ];    
        foreach ($patterns as $find => $replace) {
            $finnish = str_replace($find, $replace, $finnish);
        }
        return $finnish;
    }
    public function receive_csv_save_db($trend_group)
    {
                
        $filename = sprintf("trend_group_%d.csv", $trend_group->id);
        $to_time = time();
        $from_time = $to_time - $trend_group->query_period * 60;

        //Convet it to milisecond;
        $from_time *=1000;
        $to_time *= 1000;

        $format = "lynx --dump 'http://172.21.8.245/COSMOWEB?TYP=REGLER&MSG=GET_TRENDVIEW_DOWNLOAD_CVS&COMPUTERNR=THIS&REGLERSTRANG=%s&REZEPT=%s&FROMTIME=%d&TOTIME=%d&' > " . $filename ;
        $command = sprintf($format, $trend_group->controller_id, $trend_group->trend_group_name, $from_time, $to_time);

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

        if ( strpos( $trend_group->trend_group_name, "Freesi") !== false)  {
            if ( count($csv_data) >= 3) {
                $columns = $csv_data[0];
                $values = $csv_data[count($csv_data) - 1];
                $payload = new stdClass();
                $payload ->{"message#"} = time() - 1665171000;
                $payload -> currentTime = date_format(new DateTime(), 'c');
                $payload->measurementPoint = array();
                for ($i = 0; $i < count($values); $i++) {
                    if ( preg_match("/^[\d,]+$/", $values[$i]) ) {
                        $payload->measurementPoint[] = (object) array(
                            'controller'=>'controller',
                            'pointName'=> $this->convertFinnishtoEnglish ( $columns[$i] ),
                            'out'=> (int)str_replace(",", "", $values[$i])
                        );
                    }
                    
                }
            }
                
        }

        $output = [];
        // foreach($csv_data as $index => $arr)
        // {
        //     if ($index !=0) {
        //         $timestamp = 0;

        //         if ( count($arr) < 3 ) continue;
        //         foreach($arr as $key => $value) {

        //             if ($key == 1){
        //                 $timestamp = strtotime( trim($arr[0]). " " . trim($arr[1]));
        //             } else if ($key !=0 && $key !=1) {
        //                 if ( empty( $csv_data[0][$key] ) || empty( $value ) ) continue;
 
        //                 $data = [
        //                     'trend_group_id' => $trend_group->id,
        //                     'timestamp' => date('Y-m-d H:i:s', $timestamp),
        //                     'sensor_name' => $csv_data[0][$key],
        //                     'sensor_value' => number_format(round( floatval ( str_replace(",", ".", $value) ), 1)  , 1, '.', '')
        //                 ];
        //                 $csv_trend_data = Csv_Trend_Data::updateOrCreate(
        //                     ['trend_group_id'=> $trend_group->id, 'sensor_name' => $csv_data[0][$key]], $data
        //                 );

        //                 if ($csv_trend_data)
        //                     $output[] = $csv_trend_data;

        //             }
        //         }
        //     }
        // }

        return $csv_data;
    }
}
