<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;
use Illuminate\Support\Facades\Log;

use Exception;

class ELSYSdecoder {
    const TYPE_TEMP = 0x01; //temp 2 bytes -3276.8°C -->3276.7°C
    const TYPE_RH = 0x02; //Humidity 1 byte  0-100%
    const TYPE_ACC = 0x03; //acceleration 3 bytes X,Y,Z -128 --> 127 +/-63=1G
    const TYPE_LIGHT = 0x04; //Light 2 bytes 0-->65535 Lux
    const TYPE_MOTION = 0x05; //No of motion 1 byte  0-255
    const TYPE_CO2 = 0x06; //Co2 2 bytes 0-65535 ppm
    const TYPE_VDD = 0x07; //VDD 2byte 0-65535mV
    const TYPE_ANALOG1 = 0x08; //VDD 2byte 0-65535mV
    const TYPE_GPS = 0x09; //3bytes lat 3bytes long binary
    const TYPE_PULSE1 = 0x0A; //2bytes relative pulse count
    const TYPE_PULSE1_ABS = 0x0B; //4bytes no 0->0xFFFFFFFF
    const TYPE_EXT_TEMP1 = 0x0C; //2bytes -3276.5C-->3276.5C
    const TYPE_EXT_DIGITAL = 0x0D; //1bytes value 1 or 0
    const TYPE_EXT_DISTANCE = 0x0E; //2bytes distance in mm
    const TYPE_ACC_MOTION = 0x0F; //1byte number of vibration/motion
    const TYPE_IR_TEMP = 0x10; //2bytes internal temp 2bytes external temp -3276.5C-->3276.5C
    const TYPE_OCCUPANCY = 0x11; //1byte data
    const TYPE_WATERLEAK = 0x12; //1byte data 0-255
    const TYPE_GRIDEYE = 0x13; //65byte temperature data 1byte ref+64byte external temp
    const TYPE_PRESSURE = 0x14; //4byte pressure data (hPa)
    const TYPE_SOUND = 0x15; //2byte sound data (peak/avg)
    const TYPE_PULSE2 = 0x16; //2bytes 0-->0xFFFF
    const TYPE_PULSE2_ABS = 0x17; //4bytes no 0->0xFFFFFFFF
    const TYPE_ANALOG2 = 0x18; //2bytes voltage in mV
    const TYPE_EXT_TEMP2 = 0x19; //2bytes -3276.5C-->3276.5C
    const TYPE_EXT_DIGITAL2 = 0x1A; // 1bytes value 1 or 0
    const TYPE_EXT_ANALOG_UV = 0x1B; // 4 bytes signed int (uV)
    const TYPE_TVOC = 0x1C; // 2 bytes (ppb)
    const TYPE_DEBUG = 0x3D; // 4bytes debug

    public function bin16dec($bin) {
        $num = $bin & 0xFFFF;
        if (0x8000 & $num)
            $num = -(0x010000 - $num);
        return $num;
    }
    public function bin8dec($bin) {
        $num = $bin & 0xFF;
        if (0x80 & $num)
        $num = -(0x0100 - $num);
        return $num;
    }
    public function hexToBytes($hex) {
        $bytes = [];
        for ($c = 0; $c < strlen($hex); $c += 2)
            $bytes[]=hexdec(substr($hex, $c, 2));
        return $bytes;
    }
    
    public function DecodeElsysPayload($data) {
        $obj = [];
        for ($i = 0; $i < count($data); $i++) {

            switch ($data[$i]) {
              case self::TYPE_TEMP: //Temperature
                  $temp = ($data[$i + 1] << 8) | ($data[$i + 2]);
                  $temp = $this->bin16dec($temp);
                  $obj['temperature'] = $temp / 10;
                  $i += 2;
                  break;
              case self::TYPE_RH: //Humidity
                  $rh = ($data[$i + 1]);
                  $obj['humidity'] = $rh;
                  $i += 1;
                  break;
              case self::TYPE_ACC: //Acceleration
                  $obj['x'] = $this->bin8dec($data[$i + 1]);
                  $obj['y'] = $this->bin8dec($data[$i + 2]);
                  $obj['z'] = $this->bin8dec($data[$i + 3]);
                  $i += 3;
                  break;
              case self::TYPE_LIGHT: //Light
                  $obj['light'] = ($data[$i + 1] << 8) | ($data[$i + 2]);
                  $i += 2;
                  break;
              case self::TYPE_MOTION: //Motion sensor(PIR)
                  $obj['motion'] = ($data[$i + 1]);
                  $i += 1;
                  break;
              case self::TYPE_CO2: //CO2
                  $obj['co2'] = ($data[$i + 1] << 8) | ($data[$i + 2]);
                  $i += 2;
                  break;
              case self::TYPE_VDD: //Battery level
                  $obj['vdd'] = (($data[$i + 1] << 8) | ($data[$i + 2])) / 100;
                  $i += 2;
                  break;
              case self::TYPE_ANALOG1: //Analog input 1
                  $obj['analog1'] = ($data[$i + 1] << 8) | ($data[$i + 2]);
                  $i += 2;
                  break;
              case self::TYPE_GPS: //gps
                  $i++;
                  $obj['lat'] = ($data[$i + 0] | $data[$i + 1] << 8 | $data[$i + 2] << 16 | ($data[$i + 2] & 0x80 ? 0xFF << 24 : 0)) / 10000;
                  $obj['long'] = ($data[$i + 3] | $data[$i + 4] << 8 | $data[$i + 5] << 16 | ($data[$i + 5] & 0x80 ? 0xFF << 24 : 0)) / 10000;
                  $i += 5;
                  break;
              case self::TYPE_PULSE1: //Pulse input 1
                  $obj['pulse1'] = ($data[$i + 1] << 8) | ($data[$i + 2]);
                  $i += 2;
                  break;
              case self::TYPE_PULSE1_ABS: //Pulse input 1 absolute value
                  $pulseAbs = ($data[$i + 1] << 24) | ($data[$i + 2] << 16) | ($data[$i + 3] << 8) | ($data[$i + 4]);
                  $obj['pulseAbs'] = $pulseAbs;
                  $i += 4;
                  break;
              case self::TYPE_EXT_TEMP1: //External temp
                  $temp = ($data[$i + 1] << 8) | ($data[$i + 2]);
                  $temp = $this->bin16dec($temp);
                  $obj['externalTemperature'] = $temp / 10;
                  $i += 2;
                  break;
              case self::TYPE_EXT_DIGITAL: //Digital input
                  $obj['digital'] = ($data[$i + 1]);
                  $i += 1;
                  break;
              case self::TYPE_EXT_DISTANCE: //Distance sensor input
                  $obj['distance'] = ($data[$i + 1] << 8) | ($data[$i + 2]);
                  $i += 2;
                  break;
              case self::TYPE_ACC_MOTION: //Acc motion
                  $obj['accMotion'] = ($data[$i + 1]);
                  $i += 1;
                  break;
              case self::TYPE_IR_TEMP: //IR temperature
                  $iTemp = ($data[$i + 1] << 8) | ($data[$i + 2]);
                  $iTemp = $this->bin16dec($iTemp);
                  $eTemp = ($data[$i + 3] << 8) | ($data[$i + 4]);
                  $eTemp = $this->bin16dec($eTemp);
                  $obj['irInternalTemperature'] = $iTemp / 10;
                  $obj['irExternalTemperature'] = $eTemp / 10;
                  $i += 4;
                  break;
              case self::TYPE_OCCUPANCY: //Body occupancy
                  $obj['occupancy'] = ($data[$i + 1]);
                  $i += 1;
                  break;
              case self::TYPE_WATERLEAK: //Water leak
                  $obj['waterleak'] = ($data[$i + 1]);
                  $i += 1;
                  break;
              case self::TYPE_GRIDEYE: //Grideye data
                  $ref = $data[$i+1];
                  $i++;
                  $obj['grideye'] = [];
                  for($j = 0; $j < 64; $j++) {
                      $obj['grideye'][$j] = $ref + ($data[1+$i+$j] / 10.0);
                  }
                  $i += 64;
                  break;
              case self::TYPE_PRESSURE: //External Pressure
                  $temp = ($data[$i + 1] << 24) | ($data[$i + 2] << 16) | ($data[$i + 3] << 8) | ($data[$i + 4]);
                  $obj['pressure'] = $temp / 1000;
                  $i += 4;
                  break;
              case self::TYPE_SOUND: //Sound
                  $obj['soundPeak'] = $data[$i + 1];
                  $obj['soundAvg'] = $data[$i + 2];
                  $i += 2;
                  break;
              case self::TYPE_PULSE2: //Pulse 2
                  $obj['pulse2'] = ($data[$i + 1] << 8) | ($data[$i + 2]);
                  $i += 2;
                  break;
              case self::TYPE_PULSE2_ABS: //Pulse input 2 absolute value
                  $obj['pulseAbs2'] = ($data[$i + 1] << 24) | ($data[$i + 2] << 16) | ($data[$i + 3] << 8) | ($data[$i + 4]);
                  $i += 4;
                  break;
              case self::TYPE_ANALOG2: //Analog input 2
                  $obj['analog2'] = ($data[$i + 1] << 8) | ($data[$i + 2]);
                  $i += 2;
                  break;
              case self::TYPE_EXT_TEMP2: //External temp 2
                  $temp = ($data[$i + 1] << 8) | ($data[$i + 2]);
                  $temp = $this->bin16dec($temp);

                  if(isset($obj['externalTemperature2'] ) &&  is_numeric($obj['externalTemperature2'] ) ) {
                      $obj['externalTemperature2'] = [$obj['externalTemperature2']];
                  } 
                  if(isset($obj['externalTemperature2'] ) &&  is_array($obj['externalTemperature2']) ) {
                      array_push($obj['externalTemperature2'], $temp/10);

                  } else {
                      $obj['externalTemperature2'] = $temp / 10;
                  }
                  $i += 2;
                  break;
              case self::TYPE_EXT_DIGITAL2: //Digital input 2
                  $obj['digital2'] = ($data[$i + 1]);
                  $i += 1;
                  break;
              case self::TYPE_EXT_ANALOG_UV: //Load cell analog uV
                  $obj['analogUv'] = ($data[$i + 1] << 24) | ($data[$i + 2] << 16) | ($data[$i + 3] << 8) | ($data[$i + 4]);
                  $i += 4;
                  break;
              case self::TYPE_TVOC:
                  $obj['tvoc'] = ($data[$i + 1] << 8) | ($data[$i + 2]);
                  $i += 2;
                  break;
              default: //somthing is wrong with data
                  echo 'default';
                  $i = count($data);
                  break;
            }
        }
        return $obj;
    }
}

class LorawanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        
        $request = json_decode(file_get_contents("lora.json"));
        $ELSYSdecoder = new ELSYSdecoder();
        $hexvalue  = $ELSYSdecoder->hexToBytes($request->DevEUI_uplink->payload_hex);
        
        $data = $ELSYSdecoder->DecodeElsysPayload($hexvalue);

            foreach ( $data as $key => $val ) {

                if ( $key == 'externalTemperature2' ){
                    foreach ($val as $key1 => $val1){
                        $sensorKey = $key."_".strval($key1);
                        $sensorValue = $val1;

                        $dbdata = array(
                            'deviceId' => $request->DevEUI_uplink->DevEUI,
                            'type' => $sensorKey,
                            'observationId' => null,
                            'tag' => '',
                            'name' => '',
                            'unit' => '',
                            'value' => strval($sensorValue),
                            'message_time' => $request->DevEUI_uplink->Time,
                        );
        
                        Sensor::updateOrCreate(
                            ['deviceId' => $request->DevEUI_uplink->DevEUI, 'type' => $sensorKey] , $dbdata
                        );                       

                    }
                } else {
                    $sensorKey = $key;
                    $sensorValue = $val;

                    $dbdata = array(
                        'deviceId' => $request->DevEUI_uplink->DevEUI,
                        'type' => $sensorKey,
                        'observationId' => null,
                        'tag' => '',
                        'name' => '',
                        'unit' => '',
                        'value' => strval($sensorValue),
                        'message_time' => $request->DevEUI_uplink->Time,
                    );

                    Sensor::updateOrCreate(
                        ['deviceId' => $request->DevEUI_uplink->DevEUI, 'type' => $sensorKey] , $dbdata
                    );   

                }
            }
            return $data;

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
    public function receive_data(Request $request)
    {

        try{
            file_put_contents("lora.json", json_encode($request->all()));

            $ELSYSdecoder = new ELSYSdecoder();
            $hexvalue  = $ELSYSdecoder->hexToBytes($request['DevEUI_uplink']['payload_hex']);
            $data = $ELSYSdecoder->DecodeElsysPayload($hexvalue);

            
            
            // foreach ( $data as $key => $val ) {
            //     if ( $key == 'externalTemperature2' ){
            //         foreach ($val as $key1 => $val1){
            //             $sensorKey = $key."_".strval($key1);
            //             $sensorValue = $val1;

            //             $dbdata = array(
            //                 'deviceId' => $request['DevEUI_uplink']['DevEUI'],
            //                 'type' => $sensorKey,
            //                 'observationId' => '',
            //                 'tag' => '',
            //                 'name' => '',
            //                 'unit' => '',
            //                 'value' => strval($sensorValue),
            //                 'message_time' => $request['DevEUI_uplink']['Time'],
            //             );
        
            //             Sensor::updateOrCreate(
            //                 ['deviceId' => $request['DevEUI_uplink']['DevEUI'], 'type' => $sensorKey] , $dbdata
            //             ); 
                        
            //             Log::debug('An Lora data');
            //             Log::debug(print_r($dbdata, true));
            //         }
            //     } else {
            //         $sensorKey = $key;
            //         $sensorValue = $val;

            //         $dbdata = array(
            //             'deviceId' => $request['DevEUI_uplink']['DevEUI'],
            //             'type' => $sensorKey,
            //             'observationId' => '',
            //             'tag' => '',
            //             'name' => '',
            //             'unit' => '',
            //             'value' => strval($sensorValue),
            //             'message_time' => $request['DevEUI_uplink']['Time'],
            //         );

            //         Sensor::updateOrCreate(
            //             ['deviceId' => $request['DevEUI_uplink']['DevEUI'], 'type' => $sensorKey] , $dbdata
            //         );   
            //         Log::debug('An Lora data');
            //         Log::debug(print_r($dbdata, true));
            //     }
            // }
            
        }catch(Exception $e){
            return response()->json([
                'error' => $e->getMessage()
            ], 403);
        }

        return response()->json([
            'success' => "Received Data"
        ], 200);
    }
}
