<?php

namespace App\Http\Traits;

use DateTime;
use phpseclib3\Net\SFTP;
use stdClass;
use TheSeer\Tokenizer\Exception;

trait TrendDataTrait
{

    public function receive_csv_and_savefile_sendto_external_ftp($trend_group)
    {
        try {
            
            
            $currentTime = new DateTime('now');
            $date = $currentTime->format('Y_m_d');
            $now =$currentTime->format('Y_m_d_H_i_');


            if (strpos($trend_group->trend_group_name, "G_MALSKI_VAK") !== false) {

                // $date = date('Y_m_d', time());
                // $now =   date('Y_m_d_H_i_', time());

                $local_filename = str_replace(" ", "_", sprintf("%s%s%s.csv", $now, $trend_group->trend_group_name, $trend_group->controller_id));

                $local_folderpath = sprintf("storage/%s/", $date);

                if (! file_exists($local_folderpath)) {
                    mkdir($local_folderpath, 0777, true);
                }

                $to_time = time();
                $from_time = $to_time - $trend_group->query_period * 60;

                //Convet it to milisecond;
                $from_time *= 1000;
                $to_time *= 1000;

                $format = "lynx --dump 'http://172.21.8.245/COSMOWEB?TYP=REGLER&MSG=GET_TRENDVIEW_DOWNLOAD_CVS&COMPUTERNR=THIS&REGLERSTRANG=%s&REZEPT=%s&FROMTIME=%d&TOTIME=%d&' > " . $local_folderpath . $local_filename;
                $command = sprintf($format, $trend_group->controller_id, $trend_group->trend_group_name, $from_time, $to_time);
                exec($command, $output, $return_var);
                $local_storage_path = str_replace(" ", "_", sprintf("%s/%s%s%s.csv", $date, $now, $trend_group->trend_group_name, $trend_group->controller_id));
                // $sftp = Storage::disk('sftp');

                $remote_storage_path = sprintf("%s%s%s.csv", $now, $trend_group->trend_group_name, $trend_group->controller_id);
                // $sftp->put($remote_storage_path, file_get_contents(storage_path($local_storage_path)));
                $sftp = new SFTP('sftp.granlund.fi');
                $sftp->login('LahdenMalski_Deos_Metrix', 'D!7kbaBA4U-sKhU7');
                $sftp->put($remote_storage_path, file_get_contents(storage_path($local_storage_path)));
                return [
                    'message' => $local_storage_path . " sent successfully"
                ];
                file_put_contents("error.log", $local_storage_path . " sent successfully" . PHP_EOL  , FILE_APPEND);
            } else if (strpos($trend_group->trend_group_name, "Vesimittaukset") !== false) {

                $to_time = time();
                $from_time = $to_time - $trend_group->query_period * 60;
                $filename = sprintf("%s_%s.csv", $trend_group->trend_group_name, date_format(new DateTime(), 'His'));
                $csv_data = [];
        
                //Convet it to milisecond;
                $from_time *= 1000;
                $to_time *= 1000;
                $httpcode = 0;
        
                $starttime = microtime(true);
                $time_taken = 0;
        
                $format = "lynx --dump 'http://172.21.8.245/COSMOWEB?TYP=REGLER&MSG=GET_TRENDVIEW_DOWNLOAD_CVS&COMPUTERNR=THIS&REGLERSTRANG=%s&REZEPT=%s&FROMTIME=%d&TOTIME=%d&' > " . $filename;
                $command = sprintf($format, $trend_group->controller_id, $trend_group->trend_group_name, $from_time, $to_time);

                if (file_exists($filename)) {
                    unlink($filename);
                }
                exec($command, $output, $return_var);
                $time_taken = microtime(true) - $starttime;
    
                $file = fopen($filename, "r");
    
                $index = 0;
                while (! feof($file)) {
                    $index++;
                    $row = fgetcsv($file, 0, ';');
                    if (is_array($row)) {
                        $csv_data[] = $row;
                    }
    
                }
                fclose($file);
    
                if (file_exists($filename)) {
                    unlink($filename);
                }


                return $csv_data;

                $local_storage_path = sprintf("%s/%s_%s_%s.csv", $date, $trend_group->location_name, $trend_group->trend_group_name, $currentTime->format('Y-m-d-H-i-s'));
                $remote_storage_path = sprintf("%s_%s_%s.csv", $trend_group->location_name, $trend_group->trend_group_name, $currentTime->format('Y-m-d-H-i-s'));

                //Write csv file again 


                $handle = fopen(storage_path($local_storage_path), 'a');
                fputcsv($handle, ['MeterID', 'ValueDateTime', 'Value'], ';');
                for ($i= 0; $i<count($csv_data)-1; $i++) {
                    $location_cnt = count($csv_data[0]) -2;
                    
                    for ($j = 0; $j<$location_cnt-2; $j++) {
                        $line= [];
                        //add meterID
                        $line[] = $csv_data[0][$j + 2];
                        //add ValueDateTime
                        // 2.12.2023 19:10:00
                        echo $csv_data[$i+1][0] . ' ' .  $csv_data[$i+1][1];
                        $dateObj = DateTime::createFromFormat('j.n.Y H:i:s', $csv_data[$i+1][0] . ' ' .  $csv_data[$i+1][1]);
                        $line[] = $dateObj->format('Y-m-d\TH:i:s\Z');
                        //add Value
                        $line[] = str_replace(",", ".", $csv_data[$i+1][$j + 2]);
                        fputcsv($handle, $line, ';');
                    }
                }
                fclose($handle);

                //now we send the file to ftp server
                $ftp_server = "194.142.156.67";
                $ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");

                $login = ftp_login($ftp_conn, "163098", "r8ZL~SRk&r?{SsG");
                if (!$login) {
                    die("Could not log in");
                }

                if (ftp_put($ftp_conn, $remote_storage_path, storage_path($local_storage_path))) {
                    file_put_contents("error.log", $local_storage_path . " sent successfully" . PHP_EOL  , FILE_APPEND);
                    return ['message' => $local_storage_path . " sent successfully"];
                } else {
                    file_put_contents("error.log", "Error uploading $local_storage_path." . PHP_EOL  , FILE_APPEND);  
                    return ['message' => "Error uploading $local_storage_path"];
                }
                
                // return $csv_data;
            }

        } catch (Exception $ex) {

            file_put_contents("error.log", $ex->getMessage() . PHP_EOL , FILE_APPEND);
            return [
                'message' => 'Something went wrong ' . $ex->getMessage()
            ];
        }

    }
    public function convertFinnishtoEnglish($finnish)
    {
        $patterns = [
            'ä' => 'a',
            'ö' => 'o',
            'Ä' => 'A',
            'Ö' => 'O',
            ' ' => '_',
        ];
        foreach ($patterns as $find => $replace) {
            $finnish = str_replace($find, $replace, $finnish);
        }
        return $finnish;
    }
    public function receive_csv_save_db($trend_group)
    {
        $payload = new stdClass();
        $payload->{"message#"} = time() - 1665428000;
        $payload->currentTime = date_format(new DateTime(), 'Y-m-d H:i:s.vO');
        $payload->measurementPoint = array();

        $to_time = time();
        $from_time = $to_time - $trend_group->query_period * 60;
        $filename = sprintf("%s_%s.csv", $trend_group->trend_group_name, date_format(new DateTime(), 'His'));
        $csv_data = [];

        //Convet it to milisecond;
        $from_time *= 1000;
        $to_time *= 1000;
        $httpcode = 0;

        $starttime = microtime(true);
        $time_taken = 0;

        $format = "lynx --dump 'http://172.21.8.245/COSMOWEB?TYP=REGLER&MSG=GET_TRENDVIEW_DOWNLOAD_CVS&COMPUTERNR=THIS&REGLERSTRANG=%s&REZEPT=%s&FROMTIME=%d&TOTIME=%d&' > " . $filename;
        $command = sprintf($format, $trend_group->controller_id, $trend_group->trend_group_name, $from_time, $to_time);

        try {

            if (file_exists($filename)) {
                unlink($filename);
            }
            exec($command, $output, $return_var);
            $time_taken = microtime(true) - $starttime;

            $file = fopen($filename, "r");

            $index = 0;
            while (! feof($file)) {
                $index++;
                $row = fgetcsv($file, 0, ';');
                if (is_array($row)) {
                    $csv_data[] = $row;
                }

            }
            fclose($file);

            if (file_exists($filename)) {
                unlink($filename);
            }

            if (strpos($trend_group->trend_group_name, "Freesi") !== false) {
                if (count($csv_data) >= 2) {
                    $columns = $csv_data[0];
                    $values = $csv_data[count($csv_data) - 1];

                    for ($i = 0; $i < count($values); $i++) {
                        if (preg_match("/^[\d,-]+$/", $values[$i])) {
                            $payload->measurementPoint[] = (object) array(
                                'controller' => $trend_group->trend_group_name,
                                'pointName' => $this->convertFinnishtoEnglish($columns[$i]),
                                'out' => (float) str_replace(",", ".", $values[$i]),
                                'facet' => '',
                            );
                        }
                    }

                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://freesi-sensor-data-hub.azure-devices.net/devices/hka_vipusenkatu5a/messages/events?api-version=2018-06-30',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => json_encode($payload),
                        CURLOPT_HTTPHEADER => array(
                            'authorization: SharedAccessSignature sr=freesi-sensor-data-hub.azure-devices.net%2Fdevices%2Fhka_vipusenkatu5a&sig=BIW3fIdoAL70h6tw0Bj0dyXFrd%2BIg7eN3ctYZZnNltc%3D&se=2024431159',
                            'Content-Type: application/json',
                        ),
                    ));

                    $response = curl_exec($curl);
                    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    curl_close($curl);
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

        } catch (\Exception $e) {

            $msg = sprintf("%s               %s\n", $filename, $e->getMessage());
            // Write the contents to the file,
            // using the FILE_APPEND flag to append the content to the end of the file
            // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
            // file_put_contents('logs.txt', $msg, FILE_APPEND | LOCK_EX);
        }

        $msg = sprintf("%s               %d\n", $filename, $httpcode);
        // Write the contents to the file,
        // using the FILE_APPEND flag to append the content to the end of the file
        // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
        // file_put_contents('logs.txt', json_encode( $payload ) . " " . $httpcode . " " . $time_taken . "\n", FILE_APPEND | LOCK_EX);
        if ($httpcode == 0) {
            // file_put_contents('logs.txt', json_encode( $csv_data ) .  "\n", FILE_APPEND | LOCK_EX);
        }
        return $csv_data;
    }
}