<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;
use Illuminate\Support\Facades\Log;
use App\Models\SensorLog;
use DateTime;
use Exception;

class ELSYSdecoder
{
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
    const AES_KEY = '4ADB475FFCFBC1EE09BDB7CE4A47C555';

    public function bin16dec($bin)
    {
        $num = $bin & 0xFFFF;
        if (0x8000 & $num)
            $num = -(0x010000 - $num);
        return $num;
    }
    public function twoBytestoDecimal($byte1, $byte2)
    {
        if ($byte1 >= 128) {
            //if byte1 is greater than 128, then the first bit is 1, so the value should be negative
            $p = 65536 - ($byte1 * 256 + $byte2);
            return -$p;
        } else {
            return $byte1 * 256 + $byte2;
        }
    }
    public function bin8dec($bin)
    {
        $num = $bin & 0xFF;
        if (0x80 & $num)
            $num = -(0x0100 - $num);
        return $num;
    }
    public function hexToBytes($hex)
    {
        $bytes = [];
        for ($c = 0; $c < strlen($hex); $c += 2)
            $bytes[] = hexdec(substr($hex, $c, 2));
        return $bytes;
    }

    public function DecodeElsysPayload($data)
    {
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
                    $obj['vdd'] = (($data[$i + 1] << 8) | ($data[$i + 2])) / 1000;
                    $obj['vdd'] = round($obj['vdd'], 1);
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
                    $ref = $data[$i + 1];
                    $i++;
                    $obj['grideye'] = [];
                    for ($j = 0; $j < 64; $j++) {
                        $obj['grideye'][$j] = $ref + ($data[1 + $i + $j] / 10.0);
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

                    if (isset($obj['externalTemperature2']) && is_numeric($obj['externalTemperature2'])) {
                        $obj['externalTemperature2'] = [$obj['externalTemperature2']];
                    }
                    if (isset($obj['externalTemperature2']) && is_array($obj['externalTemperature2'])) {
                        array_push($obj['externalTemperature2'], $temp / 10);

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

    function encrypt($string, $key)
    {

        // openssl_encrypt encrypts different Mcrypt, the key length requirement, the encryption result does not exceed 16
        $data = openssl_encrypt($string, 'AES-128-ECB', self::AES_KEY, OPENSSL_RAW_DATA);
        $data = strtolower(bin2hex($data));
        return $data;
    }

    /**
     * @param string $ string The string to be decrypted
     * @param string $ key key
     * @return string
     */
    function decrypt($string, $key)
    {
        $decrypted = openssl_decrypt(hex2bin($string), 'AES-128-ECB', self::AES_KEY, OPENSSL_RAW_DATA);

        return $decrypted;

    }
}
class ZENNERDecoder
{
    private $alarms = [
        "0200" => 'BatteryEndOfLife',
        "0800" => 'SmokeChamberPollutionPrewarning',
        "1000" => 'SmokeChamberPollutionWarning',
        "2000" => 'TestButtonFailure',
        "4000" => 'AcousticAlarmFailure',
        "8000" => 'RemovalDetection',
        "0001" => 'TestAlarm',
        "0002" => 'SmokeAlarm',
        "0004" => 'ObstructionDetection',
        "0008" => 'SurroundingAreaMonitoring',
        "0010" => 'LEDFailure'
    ];
    private $errorCodes = [
        "02" => "Removal",
        "0C" => "Battery end of life",
        "16" => "Horn drive level failure",
        "1A" => "Obstruction detection",
        "19" => "Smoke alarm released (only in some CommunicationScenarios)",
        "1C" => "Object in the surrounding area"
    ];

    private $statusBits = [
        0 => "Removal detected",
        2 => "Battery end of life",
        3 => "Acoustic alarm failure",
        4 => "Obstruction detection",
        5 => "Surrounding area monitoring"
    ];

    public function decodeUplink($input)
    {
        $d = [
            'value1' => [],
            'value2' => []
        ];
        $warnings = [];
        $errors = [];

        $d['transceived_at'] = $input['transceived_at'] ?? date('c');
        // $b = array_map(function ($byte) {
        //     return strtoupper(str_pad(chr($byte), 2, '0', STR_PAD_LEFT));
        // }, $input['bytes']);
        $b = str_split(strtoupper( $input['bytes'] ), 2);
        print_r($b);
        if ($b[0] === "11") {
            $this->decodePacketSP1_1($b, $d, $warnings);
        }

        if ($b[0] === "91") {
            $this->decodePacketSP9_1($b, $d, $warnings);
        }

        if ($b[0] === "92") {
            $this->decodePacketSP9_2($b, $d);
        }

        if ($b[0] === "A0") {
            $this->decodePacketAP1_0($b, $d, $warnings, $errors);
        }

        return ['data' => $d, 'warnings' => $warnings, 'errors' => $errors];
    }
    private function decodePacketSP1_1($b, &$d, &$warnings)
    {
        $d['packetType'] = "SP1.1";
        $d['packetName'] = "day value";
        $d['message_art'] = "Status report";
        $alarmResult = $this->getAlarmFromHex(implode('', array_slice($b, 1, 2)));
        if ($alarmResult === "OKAY") {
            $d['status'] = 1;
        } else {
            $d['status'] = 2;
            $d['warning_current'] = $alarmResult;
            $warnings['message'] = $alarmResult;
        }
    }
    private function decodePacketSP9_1($b, &$d, &$warnings)
    {
        $d['packetType'] = "SP9.1";
        $d['packetName'] = "current date and time & status summary";
        $d['message_art'] = "Status report";
        $d['timestamp'] = $this->datetime(implode('', array_reverse(array_slice($b, 1, 4))));
        $statussummary = implode(' ', array_slice($b, 5, 2));
        if ($statussummary === "00 00") {
            $d['status'] = 1;
        } else {
            $d['status'] = 2;
            $warningResult = $this->decodeRadioStatus($statussummary);
            $d['warning_current'] = $warningResult;
            $warnings['message'] = $warningResult;
        }
    }
    private function decodePacketSP9_2($b, &$d)
    {
        $d['packetType'] = "SP9.2";
        $d['packetName'] = "static device information";
        $d['message_art'] = "Status report";

        $firmwareVersion = array_map(function($n) {
          return intval($n);
        }, array_reverse(array_slice($b, 1, 4)));

        $d['firmwareVersion'] = implode('.', $firmwareVersion);

      // Convert and format the LoRaWAN version
      $loraWanVersion = array_map(function($n) {
        return intval($n);
      }, array_reverse(array_slice($b, 5, 3)));
      $d['loraWanVersion'] = implode('.', $loraWanVersion);

      // Convert and format the LoRa command version
      $loraCommandVersion = array_map(function($n) {
        return intval($n);
      }, array_reverse(array_slice($b, 8, 2)));
      $d['loraCommandVersion'] = implode('.', $loraCommandVersion);

      // Convert and format the Minol device type
      $minolDeviceType = array_map(function($n) {
        return intval($n);
      }, str_split($b[10], 2));
      $d['minolDeviceType'] = implode('.', $minolDeviceType);

      // Parse device meter ID
      $meterIdBytes = array_slice($b, 11, 4);
      $d['meterId'] = intval(implode('', array_reverse($meterIdBytes)), 16);

        // Versionsnummern und Geräte-IDs müssen noch implementiert werden
    }
    private function decodePacketAP1_0($b, &$d, &$warnings, &$errors)
    {
        $d['packetType'] = "AP1.0";
        $d['packetName'] = "status code, status data";
        $d['message_art'] = "malfunction report";
        $d['timestamp'] = $this->datetime(implode('', array_reverse(array_slice($b, 3, 2))));
        $d['status'] = 2;
        $apCodeResult = $this->APCode($b[1]);
        $d['warning_current'] = $apCodeResult;
        $warnings['message'] = $apCodeResult;
        $errors['message'] = $apCodeResult;
    }
    private function getAlarmFromHex($hexValue)
    {
        $lsbHexValue = substr($hexValue, -2) . substr($hexValue, 0, -2);
        $decimalValue = hexdec($lsbHexValue);
        $alarms = array_filter($this->alarms, function ($key) use ($decimalValue) {
            return ($decimalValue & hexdec($key));
        }, ARRAY_FILTER_USE_KEY);

        return implode(", ", $alarms) ?: "OKAY";
    }
    private function decodeRadioStatus($payload)
    {
        $decimalPayload = hexdec($payload);
        $statuses = array_filter($this->statusBits, function ($bit) use ($decimalPayload) {
            return ($decimalPayload & (1 << $bit));
        }, ARRAY_FILTER_USE_KEY);

        return implode(", ", $statuses);
    }
    private function APCode($payload)
    {
        $results = array_filter($this->errorCodes, function ($code) use ($payload) {
            return strpos($payload, $code) !== false;
        }, ARRAY_FILTER_USE_KEY);

        return implode(", ", $results);
    }
    private function datetime($hex)
    {
        var_dump($hex);
        $hex = hexdec($hex);
        var_dump($hex);
        $dt0 = $hex & 0xff;
        $dt1 = ($hex >> 8) & 0xff;
        $dt2 = ($hex >> 16) & 0xff;
        $dt3 = ($hex >> 24) & 0xff;
        
        $year = (($dt2 & 0xE0) >> 5) | (($dt3 & 0xF0) >> 1) + 2000;
        $month = ($dt3 & 0x0F) - 1;
        $day = $dt2 & 0x1F;
        $hour = $dt1 & 0x1F;
        $minute = $dt0 & 0x3F;
        
        // Create DateTime object
        $date = new DateTime();
        $date->setDate($year, $month + 1, $day); // PHP months are 1-based, JavaScript months are 0-based
        $date->setTime($hour, $minute);

        return $date->format('c');
    }
}
class Solidusdecoder extends ELSYSdecoder
{

    public function DecodeSolidusPayload($data)
    {
        $obj = [];
        $obj['vdd'] = $data[0] * 30;
        $obj['temperature'] = $data[1] - 128;
        $obj['MSB'] = round($data[2] / 240, 3);
        $obj['LSB'] = round($data[3] / 240, 3);

        $temp = $data[2] * 256 + $data[3];
        if ($temp >= 32768) {
            $temp = $temp - 65535;
        }
        $obj['Pressure'] = round($temp / 240, 3);
        return $obj;
    }

}
class IOTSUdecoder extends ELSYSdecoder
{
    public function calctVOC($val)
    {
        if ($val <= 30) {
            return 2 * $val;
        } else if ($val <= 118) {
            return (30 - 0) * 2 + ($val - 30) * 5;
        } else if ($val <= 193) {
            return (30 - 0) * 2 + (118 - 30) * 5 + ($val - 118) * 20;
        } else {
            return (30 - 0) * 2 + (118 - 30) * 5 + (193 - 118) * 20 + ($val - 193) * 100;
        }
        return 0;
    }
    public function DecodeIOTSUPayload($data, $model = '')
    {
        $obj = [];
        if (strpos($model, 'l2aq05') !== false || strpos($model, 'l3aq05') !== false) {

            $obj['battery voltage'] = $data[0] * 20;
            // $obj['humidity #1'] = $data[2] >> 1;
            // $obj['humidity #2'] = $data[5] >> 1;
            // $obj['humidity #3'] = $data[8] >> 1;
            $obj['humidity'] = $data[11] >> 1;

            // $obj['temperature #1'] = ((($data[2] % 2) << 8) + $data[3])  / 10;
            // $obj['temperature #2'] = ((($data[5] % 2) << 8 )+ $data[6]) / 10;
            // $obj['temperature #3'] = ((($data[8] % 2) << 8 )+ $data[9]) / 10;
            $obj['temperature'] = ((($data[11] % 2) << 8) + $data[12]) / 10;

            // $obj['co2 #1'] = $data[4] * 10 + 400;
            // $obj['co2 #2'] = $data[7] * 10 + 400;
            // $obj['co2 #3'] = $data[10] * 10 + 400;
            $obj['co2'] = $data[13] * 10 + 400;

        } else if (strpos($model, 'l2aq01') !== false || strpos($model, 'l3aq01') !== false) {
            $obj['battery voltage'] = $data[0] * 20;
            // $obj['humidity #1'] = $data[2] >> 1;
            // $obj['humidity #2'] = $data[6] >> 1;
            // $obj['humidity #3'] = $data[10] >> 1;
            $obj['humidity'] = $data[14] >> 1;


            // $obj['temperature #1'] = ((($data[2] % 2) << 8) + $data[3]) / 10;
            // $obj['temperature #2'] = ((($data[6] % 2) << 8)+ $data[7]) / 10;
            // $obj['temperature #3'] = ((($data[10] % 2) << 8) + $data[11]) / 10;
            $obj['temperature'] = ((($data[14] % 2) << 8) + $data[15]) / 10;




            // $obj['co2 #1'] = $data[4] * 10 + 400;
            // $obj['co2 #2'] = $data[8] * 10 + 400;
            // $obj['co2 #3'] = $data[12] * 10 + 400;
            $obj['co2'] = $data[16] * 10 + 400;

            // $obj['tvoc #1'] = $this->calctVOC($data[5]);
            // $obj['tvoc #2'] = $this->calctVOC($data[9]);
            // $obj['tvoc #3'] = $this->calctVOC($data[13]);
            $obj['tvoc'] = $this->calctVOC($data[17]);

        } else if (strpos($model, 'l2dp01_v1') !== false) {
            $obj['pa'] = ($this->twoBytestoDecimal($data[8], $data[9])) / 10;

        } else {
            $obj['vdd'] = $data[0] * 20;
            $obj['humidity'] = $data[2] >> 1;
            $obj['temperature'] = $data[3] / 10;
            $obj['co2'] = $data[4] * 10 + 400;
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
        $hexvalue = $ELSYSdecoder->hexToBytes($request->DevEUI_uplink->payload_hex);

        $data = $ELSYSdecoder->DecodeElsysPayload($hexvalue);

        foreach ($data as $key => $val) {

            if ($key == 'externalTemperature2') {
                foreach ($val as $key1 => $val1) {
                    $sensorKey = $key . "_" . strval($key1);
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

                    $sensor = Sensor::updateOrCreate(
                        ['deviceId' => $request->DevEUI_uplink->DevEUI, 'type' => $sensorKey], $dbdata
                    );

                    $log = SensorLog::where('sensor_id', $sensor->id)->first();
                    $log_data = array(
                        'sensor_id' => $sensor->id,
                    );
                    if (! isset($log)) {
                        $log_data['logs'] = json_encode([
                            date('Y-m-d H:i:s') => $sensor->value
                        ]);
                    } else {
                        $log_data['logs'] = (array) json_decode($log->logs);
                        $len = count($log_data['logs']);
                        if ($len > 9) {
                            $log_data['logs'] = array_slice($log_data['logs'], $len - 9);
                        }
                        $log_data['logs'][date('Y-m-d H:i:s')] = $sensor->value;

                        $log_data['logs'] = json_encode($log_data['logs']);
                    }
                    $log = SensorLog::updateOrCreate(
                        ['sensor_id' => $sensor->id,], $log_data
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

                $sensor = Sensor::updateOrCreate(
                    ['deviceId' => $request->DevEUI_uplink->DevEUI, 'type' => $sensorKey], $dbdata
                );

                $log = SensorLog::where('sensor_id', $sensor->id)->first();
                $log_data = array(
                    'sensor_id' => $sensor->id,
                );
                if (! isset($log)) {
                    $log_data['logs'] = json_encode([
                        date('Y-m-d H:i:s') => $sensor->value
                    ]);
                } else {
                    $log_data['logs'] = (array) json_decode($log->logs);
                    $len = count($log_data['logs']);
                    if ($len > 9) {
                        $log_data['logs'] = array_slice($log_data['logs'], $len - 9);
                    }
                    $log_data['logs'][date('Y-m-d H:i:s')] = $sensor->value;

                    $log_data['logs'] = json_encode($log_data['logs']);
                }
                $log = SensorLog::updateOrCreate(
                    ['sensor_id' => $sensor->id,], $log_data
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
    public function receive_csvfile()
    {
        if (! isset($_GET['controller_id']) || ! isset($_GET['trend_group_name']) || ! isset($_GET['query_period']))
            return response()->json([
                'error' => 'Parameters are missing'
            ], 401);

        $query_period = $_GET['query_period'];
        $controller_id = $_GET['controller_id'];
        $trend_group_name = $_GET['trend_group_name'];

        $filename = "myfile.csv";
        $format = "lynx --dump 'http://172.21.8.245/COSMOWEB?TYP=REGLER&MSG=GET_TRENDVIEW_DOWNLOAD_CVS&COMPUTERNR=THIS&REGLERSTRANG=%s&REZEPT=%s&FROMTIME=%d&TOTIME=%d&' > " . $filename;
        $to_time = time();
        $from_time = $to_time - $query_period * 60;
        $from_time *= 1000;
        $to_time *= 1000;
        $command = sprintf($format, $controller_id, $trend_group_name, $from_time, $to_time);
        if (file_exists($filename)) {
            unlink($filename);
        }
        shell_exec($command);

        $file = fopen($filename, "r");
        $output = [];
        $index = 0;
        while (! feof($file)) {
            $index++;
            $row = fgetcsv($file, 0, ';');
            if (is_array($row))
                $output[] = $row;
        }
        fclose($file);

        return [
            'command' => $command,
            'data' => $output
        ];
    }
    public function receive_data(Request $request)
    {

        try {
            $request_data = $request->DevEUI_uplink;

            if (file_put_contents(sprintf("logs/%s.json", $request_data['DevEUI']), json_encode($request->all())) === false) {
                file_put_contents(sprintf("logs/%X.json", $request_data['DevEUI']), json_encode($request->all()));
            }
            $data = [];
            if (strpos($request_data['DevEUI'], "A81758FFFE") === 0) {
                $ELSYSdecoder = new ELSYSdecoder();
                $hexvalue = $ELSYSdecoder->hexToBytes($request_data['payload_hex']);
                $data = $ELSYSdecoder->DecodeElsysPayload($hexvalue);                
            }
            else if ($request_data['DevEUI'] == "47EABD48004A0044") {
                $Solidusdecoder = new Solidusdecoder();
                $hexvalue = $Solidusdecoder->hexToBytes($request_data['payload_hex']);

                $data = $Solidusdecoder->DecodeSolidusPayload($hexvalue);
            } else if (strpos($request_data['DevEUI'], "70B3D") === 0) {
                // $request_data['DevEUI'] == "70B3D55680000A6D" (L2 AQ05)
                $IOTSUdecoder = new IOTSUdecoder();
                $hexvalue = $IOTSUdecoder->hexToBytes($request_data['payload_hex']);
                $model = $request_data['CustomerData']['alr']['pro'];

                $data = $IOTSUdecoder->DecodeIOTSUPayload($hexvalue, $model);
            } else if (strpos($request_data['DevEUI'], "04B6480C01") === 0) {
                //Zenner device
                $zennerDecoder = new ZENNERDecoder();
                $input = [];
                $input['bytes'] = $request_data['payload_hex'];
                $output= $zennerDecoder->decodeUplink($input);

                $dbdata = [];
                if ($output['data']['packetType'] == "SP9.2" || $output['data']['packetType'] == "SP9.1") {
                    $dbdata = array(
                        'deviceId' => $request_data['DevEUI'],
                        'type' => $output['data']['message_art'],
                        'observationId' => null,
                        'tag' => '',
                        'name' => '',
                        'unit' => '',                        
                        'fport' => $request_data['FPort'],
                        'message_time' => $request_data['Time'],
                    );

                } else if ($output['data']['packetType'] == "SP1.1" || $output['data']['packetType'] == "AP1.0") {                   

                    $dbdata = array(
                        'deviceId' => $request_data['DevEUI'],
                        'type' => $output['data']['warning_current'] ?? $output['data']['message_art'],
                        'observationId' => null,
                        'tag' => '',
                        'name' => '',
                        'unit' => '',                        
                        'fport' => $request_data['FPort'],
                        'message_time' => $request_data['Time'],
                    );
                }

                $sensor = Sensor::updateOrCreate(
                    ['deviceId' => $request_data['DevEUI'], 'type' => $output['data']['packetType']], $dbdata
                );
                $log = SensorLog::where('sensor_id', $sensor->id)->first();

                $log_data = array(
                    'sensor_id' => $sensor->id,
                );
                if (! isset($log)) {
                    $log_data['logs'] = json_encode([
                        date('Y-m-d H:i:s') => $sensor->value
                    ]);
                } else {
                    $log_data['logs'] = (array) json_decode($log->logs);
                    $len = count($log_data['logs']);
                    if ($len > 9) {
                        $log_data['logs'] = array_slice($log_data['logs'], $len - 9);
                    }
                    $log_data['logs'][date('Y-m-d H:i:s')] = $sensor->value;

                    $log_data['logs'] = json_encode($log_data['logs']);
                }
                $log = SensorLog::updateOrCreate(
                    ['sensor_id' => $sensor->id,], $log_data
                );
            }
            foreach ($data as $key => $val) {

                if ($key == 'externalTemperature2') {
                    foreach ($val as $key1 => $val1) {
                        $sensorKey = $key . "_" . strval($key1);
                        $sensorValue = $val1;

                        $dbdata = array(
                            'deviceId' => $request_data['DevEUI'],
                            'type' => $sensorKey,
                            'observationId' => null,
                            'tag' => '',
                            'name' => '',
                            'unit' => '',
                            'value' => strval($sensorValue),
                            'fport' => $request_data['FPort'],
                            'message_time' => $request_data['Time'],
                        );

                        $sensor = Sensor::updateOrCreate(
                            ['deviceId' => $request_data['DevEUI'], 'type' => $sensorKey], $dbdata
                        );

                        $log = SensorLog::where('sensor_id', $sensor->id)->first();

                        $log_data = array(
                            'sensor_id' => $sensor->id,
                        );
                        if (! isset($log)) {
                            $log_data['logs'] = json_encode([
                                date('Y-m-d H:i:s') => $sensor->value
                            ]);
                        } else {
                            $log_data['logs'] = (array) json_decode($log->logs);
                            $len = count($log_data['logs']);
                            if ($len > 9) {
                                $log_data['logs'] = array_slice($log_data['logs'], $len - 9);
                            }
                            $log_data['logs'][date('Y-m-d H:i:s')] = $sensor->value;

                            $log_data['logs'] = json_encode($log_data['logs']);
                        }
                        $log = SensorLog::updateOrCreate(
                            ['sensor_id' => $sensor->id,], $log_data
                        );

                    }
                } else {
                    $sensorKey = $key;
                    $sensorValue = $val;

                    $dbdata = array(
                        'deviceId' => $request_data['DevEUI'],
                        'type' => $sensorKey,
                        'observationId' => null,
                        'tag' => '',
                        'name' => '',
                        'unit' => '',
                        'value' => strval($sensorValue),
                        'fport' => $request_data['FPort'],
                        'message_time' => $request_data['Time'],
                    );

                    $sensor = Sensor::updateOrCreate(
                        ['deviceId' => $request_data['DevEUI'], 'type' => $sensorKey], $dbdata
                    );

                    $log = SensorLog::where('sensor_id', $sensor->id)->first();

                    $log_data = array(
                        'sensor_id' => $sensor->id,
                    );
                    if (! isset($log)) {
                        $log_data['logs'] = json_encode([
                            date('Y-m-d H:i:s') => $sensor->value
                        ]);
                    } else {
                        $log_data['logs'] = (array) json_decode($log->logs);
                        $len = count($log_data['logs']);
                        if ($len > 9) {
                            $log_data['logs'] = array_slice($log_data['logs'], $len - 9);
                        }
                        $log_data['logs'][date('Y-m-d H:i:s')] = $sensor->value;

                        $log_data['logs'] = json_encode($log_data['logs']);
                    }

                    $log = SensorLog::updateOrCreate(
                        ['sensor_id' => $sensor->id,], $log_data
                    );
                }
            }

        } catch (Exception $e) {
            Log::debug("Lora data error");
            Log::debug($e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 403);
        }

        return response()->json([
            'success' => "Received Data"
        ], 200);
    }
}