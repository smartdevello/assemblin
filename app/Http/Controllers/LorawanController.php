<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;
use Illuminate\Support\Facades\Log;
use App\Models\SensorLog;
use App\Models\DEOS_controller;
use App\Models\Area;

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

        $tmp = explode(" ", $payload)[0];
        $decimalPayload = hexdec($tmp);


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
        $hex = hexdec($hex);
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
class NexelecDecoder
{
    private $stringHex;
    private $octetTypeProduit;
    private $octetTypeMessage;

    public function decodeUplink($input)
    {
        $this->stringHex = $input;
        $this->octetTypeProduit = hexdec(substr($this->stringHex, 0, 2));
        $this->octetTypeMessage = hexdec(substr($this->stringHex, 2, 2));

        $data = $this->dataOutput($this->octetTypeMessage);
        $errors = null;
        $warnings = null;

        return [
            'data' => $data,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    private function dataOutput($octetTypeMessage)
    {
        $outputTypeMessage = [
            $this->productStatusDataOutput(),
            $this->productConfigurationDataOutput(),
            $this->smokeAlarmDataOutput(),
            $this->dailyAirDataOutput(),
            $this->realTimeDataOutput(),
            $this->temperatureDatalogDataOutput(),
        ];

        return $outputTypeMessage[$octetTypeMessage] ?? null;
    }

    private function typeOfProduct($octetTypeProduit)
    {
        $products = [
            0xB1 => "Origin+ LoRa",
            0xB2 => "Origin LoRa",
            0xB3 => "Guard+ LoRa",
            0xB4 => "Guard LoRa",
        ];

        return $products[$octetTypeProduit] ?? "Unknown";
    }

    private function typeOfMessage($octetTypeMessage)
    {
        $messageNames = [
            "Product Status",
            "Product Configuration",
            "Smoke Alarm",
            "Air Quality",
            "Real Time",
            "Temperature datalog",
        ];

        return $messageNames[$octetTypeMessage] ?? "Unknown";
    }

    private function productStatusDataOutput()
    {
        $hwVersion = hexdec(substr($this->stringHex, 4, 2)) & 0xFF;
        $swVersion = hexdec(substr($this->stringHex, 6, 2)) & 0xFF;
        $productLifetime = hexdec(substr($this->stringHex, 8, 2)) & 0xFF;
        $smokeSensorStatus = (hexdec(substr($this->stringHex, 10, 1)) >> 3) & 0x01;
        $tempHumSensorStatus = (hexdec(substr($this->stringHex, 10, 1)) >> 2) & 0x01;
        $smokeSensorActivation = (hexdec(substr($this->stringHex, 10, 1)) >> 1) & 0x01;
        $magneticBaseDetection = (hexdec(substr($this->stringHex, 10, 2)) >> 2) & 0x07;
        $batteryLevel = hexdec(substr($this->stringHex, 11, 1)) & 0x03;
        $batteryVoltage = hexdec(substr($this->stringHex, 12, 2)) & 0xFF;

        return [
            "typeOfProduct" => $this->typeOfProduct($this->octetTypeProduit),
            "typeOfMessage" => $this->typeOfMessage($this->octetTypeMessage),
            "hwVersion" => $hwVersion,
            "swVersion" => $swVersion * 0.1,
            "rmgLifetime" => $this->productLifetime($productLifetime),
            "smokeSensorStatus" => $this->smokeStatus($smokeSensorStatus),
            "tempHumSensorStatus" => $this->temperatureStatus($tempHumSensorStatus),
            "smokeSensorActivation" => $this->smokeActivation($smokeSensorActivation),
            "antiTearDetectionStatus" => $this->magnetBaseDetection($magneticBaseDetection),
            "batteryLevel" => $this->batteryLevel($batteryLevel),
            "batteryVoltage" => $this->batteryVoltage($batteryVoltage),
        ];
    }

    private function productLifetime($octetProductLifetime)
    {
        return ["value" => $octetProductLifetime, "unit" => "month"];
    }

    private function smokeStatus($octetSmokeStatus)
    {
        $statuses = [
            0 => "Smoke sensor ok",
            1 => "Smoke sensor fault",
        ];

        return $statuses[$octetSmokeStatus] ?? "Unknown";
    }

    private function temperatureStatus($octetTemperatureStatus)
    {
        $statuses = [
            0 => "T°/humidity sensor ok",
            1 => "T°/humidity sensor fault",
        ];

        return $statuses[$octetTemperatureStatus] ?? "Unknown";
    }

    private function smokeActivation($octetSmokeActivation)
    {
        $statuses = [
            0 => "Smoke sensor deactivate",
            1 => "Smoke sensor activate",
        ];

        return $statuses[$octetSmokeActivation] ?? "Unknown";
    }

    private function magnetBaseDetection($octetMagnetBaseDetection)
    {
        $statuses = [
            0 => "Magnetic base not detected",
            1 => "Magnetic base detected",
            2 => "Product removed from its base just now",
            3 => "Product installed on its base just now",
            4 => "Magnetic base never detected",
        ];

        return $statuses[$octetMagnetBaseDetection] ?? "Unknown";
    }

    private function batteryLevel($octetBatteryLevel)
    {
        $levels = [
            0 => "High",
            1 => "Medium",
            2 => "Low",
            3 => "Critical",
        ];

        return $levels[$octetBatteryLevel] ?? "Unknown";
    }

    private function batteryVoltage($octetBatteryVoltage)
    {
        return ["value" => ($octetBatteryVoltage * 5) + 2000, "units" => "mV"];
    }

    // Additional methods (e.g., productConfigurationDataOutput, smokeAlarmDataOutput, etc.)
    // would follow the same pattern as productStatusDataOutput, translating each JS function.
	private function productConfigurationDataOutput()
	{
		$dataReconfigurationSource = (hexdec(substr($this->stringHex, 4, 1)) >> 1) & 0x07;
		$dataReconfigurationStatus = (hexdec(substr($this->stringHex, 4, 2)) >> 3) & 0x03;
		$dataDatalogEnable = (hexdec(substr($this->stringHex, 5, 1)) >> 2) & 0x01;
		$dataDailyAirEnable = (hexdec(substr($this->stringHex, 5, 1)) >> 1) & 0x01;
		$dataMagnetRemovalEnable = hexdec(substr($this->stringHex, 5, 1)) & 0x01;
		$dataPendingJoin = (hexdec(substr($this->stringHex, 6, 1)) >> 3) & 0x01;
		$dataNfcStatus = (hexdec(substr($this->stringHex, 6, 1)) >> 1) & 0x03;
		$dataLoraRegion = (hexdec(substr($this->stringHex, 6, 2)) >> 1) & 0x0F;
		$dataNbNewData = (hexdec(substr($this->stringHex, 6, 4)) >> 3) & 0x3F;
		$dataNbOfRedundancy = (hexdec(substr($this->stringHex, 9, 2)) >> 2) & 0x1F;
		$dataTransmissionPeriod = (hexdec(substr($this->stringHex, 10, 3)) >> 2) & 0xFF;
		$dataInterconnectionId = (hexdec(substr($this->stringHex, 12, 5)) >> 1) & 0x1FFFF;

		return [
			"typeOfProduct" => $this->typeOfProduct($this->octetTypeProduit),
			"typeOfMessage" => $this->typeOfMessage($this->octetTypeMessage),
			"reconfigurationSource" => $this->reconfigurationSource($dataReconfigurationSource),
			"reconfigurationStatus" => $this->reconfigurationState($dataReconfigurationStatus),
			"datalogEnable" => $this->active($dataDatalogEnable),
			"dailyAirEnable" => $this->active($dataDailyAirEnable),
			"smokeDetectionOnMagnetRemovalEnable" => $this->active($dataMagnetRemovalEnable),
			"pendingJoin" => $this->pendingJoin($dataPendingJoin),
			"nfcStatus" => $this->nfcStatus($dataNfcStatus),
			"loraRegion" => $this->loraRegion($dataLoraRegion),
			"datalogNewMeasure" => $dataNbNewData,
			"datalogMeasureRepetition" => $dataNbOfRedundancy,
			"transmissionIntervalDatalog" => $this->period($dataTransmissionPeriod),
			"d2dNetworkID" => $dataInterconnectionId,
		];
	}

	// Helper functions for decoding specific data.
	private function reconfigurationSource($value)
	{
		// Map the value to meaningful names if applicable.
		$sources = [
			0 => "Source A",
			1 => "Source B",
			2 => "Source C",
			// Add more as required.
		];
		return $sources[$value] ?? "Unknown";
	}

	private function reconfigurationState($value)
	{
		// Define mappings for reconfiguration states.
		$states = [
			0 => "State A",
			1 => "State B",
			// Add more as required.
		];
		return $states[$value] ?? "Unknown";
	}

	private function active($value)
	{
		return $value ? "Active" : "Inactive";
	}

	private function pendingJoin($value)
	{
		return $value ? "Join Pending" : "No Join Pending";
	}

	private function nfcStatus($value)
	{
		$statuses = [
			0 => "NFC Disabled",
			1 => "NFC Enabled",
			2 => "NFC Error",
			// Add more as required.
		];
		return $statuses[$value] ?? "Unknown";
	}

	private function loraRegion($value)
	{
		$regions = [
			0 => "EU868",
			1 => "US915",
			2 => "AS923",
			// Add more as required.
		];
		return $regions[$value] ?? "Unknown";
	}

	private function period($value)
	{
		return ["value" => $value, "unit" => "seconds"];
	}
	private function smokeAlarmDataOutput()
	{
		$dataSmokeAlarm = (hexdec(substr($this->stringHex, 4, 1)) >> 2) & 0x03;
		$dataSmokeHush = hexdec(substr($this->stringHex, 4, 1)) & 0x03;
		$dataSmokeTest = (hexdec(substr($this->stringHex, 5, 1)) >> 2) & 0x03;
		$dataTimeSinceLastSmokeTest = (hexdec(substr($this->stringHex, 5, 3)) >> 2) & 0xFF;
		$dataMaintenance = (hexdec(substr($this->stringHex, 7, 1)) >> 1) & 0x01;
		$dataTimeSinceLastMaintenance = (hexdec(substr($this->stringHex, 7, 3)) >> 1) & 0xFF;
		$dataTemperature = (hexdec(substr($this->stringHex, 9, 4)) >> 3) & 0x3FF;

		return [
			"typeOfProduct" => $this->typeOfProduct($this->octetTypeProduit),
			"typeOfMessage" => $this->typeOfMessage($this->octetTypeMessage),
			"smokeAlarm" => $this->alarmStatus($dataSmokeAlarm),
			"smokeAlarmHush" => $this->alarmHush($dataSmokeHush),
			"smokeLocalProductTest" => $this->smokeTest($dataSmokeTest),
			"timeSinceLastTest" => $this->periodWeek($dataTimeSinceLastSmokeTest),
			"digitalMaintenanceCertificate" => $this->maintenance($dataMaintenance),
			"timeSinceLastMaintenance" => $this->periodWeek($dataTimeSinceLastMaintenance),
			"temperature" => $this->temperature($dataTemperature),
		];
	}

	private function dailyAirDataOutput()
	{
		$dataTempMini = (hexdec(substr($this->stringHex, 4, 3)) >> 2) & 0x3FF;
		$dataTempMax = hexdec(substr($this->stringHex, 6, 3)) & 0x3FF;
		$dataTempAvg = (hexdec(substr($this->stringHex, 9, 3)) >> 2) & 0x3FF;
		$dataHumMin = (hexdec(substr($this->stringHex, 11, 3)) >> 2) & 0xFF;
		$dataHumMax = (hexdec(substr($this->stringHex, 13, 3)) >> 2) & 0xFF;
		$dataHumAvg = (hexdec(substr($this->stringHex, 15, 3)) >> 2) & 0xFF;

		return [
			"typeOfProduct" => $this->typeOfProduct($this->octetTypeProduit),
			"typeOfMessage" => $this->typeOfMessage($this->octetTypeMessage),
			"temperatureMin" => $this->temperature($dataTempMini),
			"temperatureMax" => $this->temperature($dataTempMax),
			"temperatureAvg" => $this->temperature($dataTempAvg),
			"humidityMin" => $this->humidity($dataHumMin),
			"humidityMax" => $this->humidity($dataHumMax),
			"humidityAvg" => $this->humidity($dataHumAvg),
		];
	}

	private function realTimeDataOutput()
	{
		$dataTemp = (hexdec(substr($this->stringHex, 4, 3)) >> 2) & 0x3FF;
		$dataHum = (hexdec(substr($this->stringHex, 6, 3)) >> 2) & 0xFF;

		return [
			"typeOfProduct" => $this->typeOfProduct($this->octetTypeProduit),
			"typeOfMessage" => $this->typeOfMessage($this->octetTypeMessage),
			"temperature" => $this->temperature($dataTemp),
			"humidity" => $this->humidity($dataHum),
		];
	}

	private function temperatureDatalogDataOutput()
	{
		$measure = [];
		$dataNombreMesures = (hexdec(substr($this->stringHex, 4, 2)) >> 2) & 0x3F;
		$dataTimeBetweenMeasurementSec = (hexdec(substr($this->stringHex, 5, 3)) >> 2) & 0xFF;
		$dataRepetition = hexdec(substr($this->stringHex, 7, 2)) & 0x3F;
		$binary = $this->hexToBinary($this->stringHex);

		for ($i = 0; $i < $dataNombreMesures; $i++) {
			$offsetBinaire = 36 + (10 * $i);
			$measure[$i] = bindec(substr($binary, $offsetBinaire, 10));
			//$measure[$i] = ($measure[$i] === 0x3FF) ? 0 : round(($measure[$i] / 10) - 30);
			$measure[$i] = ($measure[$i] === 0x3FF) ? 0 : number_format(($measure[$i] / 10 - 30), 2,".","");
		}

		return [
			"typeOfProduct" => $this->typeOfProduct($this->octetTypeProduit),
			"typeOfMessage" => $this->typeOfMessage($this->octetTypeMessage),
			"datalogNewMeasure" => $dataNombreMesures,
			"transmissionIntervalDatalog" => ["value" => $dataTimeBetweenMeasurementSec, "unit" => "minutes"],
			"datalogMeasureRepetition" => $dataRepetition,
			"temperature" => ["value" => $measure, "unit" => "°C"],
		];
	}
	private function alarmStatus($octetAlarmStatus)
	{
		switch ($octetAlarmStatus) {
			case 0:
				return "Smoke Alarm non-activated";
			case 1:
				return "Local smoke Alarm activated";
			case 2:
				return "Remote smoke Alarm activated";
			default:
				return "Unknown Alarm Status"; // Fallback for unexpected values
		}
	}
	// Converts alarm hush status to a descriptive message
	private function alarmHush($octetAlarmHush)
	{
		switch ($octetAlarmHush) {
			case 0:
				return "Smoke alarm stopped because no smoke anymore";
			case 1:
				return "Smoke alarm stopped following central button press";
			case 2:
				return "Smoke alarm stopped following a remote silence";
			default:
				return "Unknown Alarm Hush Status";
		}
	}

	// Converts smoke test status to a descriptive message
	private function smokeTest($octetSmokeTest)
	{
		switch ($octetSmokeTest) {
			case 0:
				return "Smoke test off";
			case 1:
				return "Local smoke test was done";
			case 2:
				return "Remote smoke test was done";
			default:
				return "Unknown Smoke Test Status";
		}
	}

	// Converts maintenance status to a descriptive message
	private function maintenance($octetMaintenance)
	{
		return $octetMaintenance === 0 ? "Maintenance not done" : "Maintenance has been done";
	}

	// Converts a number of weeks to a structured response
	private function periodWeek($octetWeek)
	{
		return ["value" => $octetWeek, "units" => "week"];
	}

	// Converts temperature octet to a structured response
	private function temperature($octetTemperature)
	{
		if ($octetTemperature == 1023) {
			return "Error";
		}
		return ["value" => ($octetTemperature * 0.1) - 30, "unit" => "°C"];
	}

	// Converts humidity octet to a structured response
	private function humidity($octetHumidity)
	{
		if ($octetHumidity == 255) {
			return "Error";
		}
		return ["value" => $octetHumidity * 0.5, "unit" => "°C"];
	}

	// Converts a hexadecimal string to a binary string
	private function hexToBinary($encoded)
	{
		$string_bin = '';
		foreach (str_split($encoded) as $char) {
			$binary = str_pad(base_convert($char, 16, 2), 4, '0', STR_PAD_LEFT);
			$string_bin .= $binary;
		}
		return $string_bin;
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
        $data = [];
        try {
            $request_data = $request->DevEUI_uplink;

            if (file_put_contents(sprintf("logs/%s.json", $request_data['DevEUI']), json_encode($request->all())) === false) {
                file_put_contents(sprintf("logs/%X.json", $request_data['DevEUI']), json_encode($request->all()));
            }
            
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


                $dbdata = array(
                    'deviceId' => $request_data['DevEUI'],
                    'type' => $output['data']['message_art'],
                    'tag' => '',
                    'name' => '',
                    'unit' => '',
                    'strValue' => $output['data']['warning_current'] ?? '',
                    'fport' => $request_data['FPort'],
                    'message_time' => $request_data['Time'],
                );


                $sensor = Sensor::updateOrCreate(
                    ['deviceId' => $request_data['DevEUI'] ], $dbdata
                );
                $log = SensorLog::where('sensor_id', $sensor->id)->first();

                $log_data = array(
                    'sensor_id' => $sensor->id,
                );
                if (! isset($log)) {
                    $log_data['logs'] = json_encode([
                        date('Y-m-d H:i:s') => $sensor->strValue,
                    ]);
                } else {
                    $log_data['logs'] = (array) json_decode($log->logs);
                    $len = count($log_data['logs']);
                    if ($len > 9) {
                        $log_data['logs'] = array_slice($log_data['logs'], $len - 9);
                    }
                    $log_data['logs'][date('Y-m-d H:i:s')] = $sensor->strValue;

                    $log_data['logs'] = json_encode($log_data['logs']);
                }
                $log = SensorLog::updateOrCreate(
                    ['sensor_id' => $sensor->id,], $log_data
                );
            } else if (strpos($request_data['DevEUI'], "70B3D540F658D536") === 0) {
                //Nexelec device
                $nexelecDecoder = new NexelecDecoder();
                $input = $request_data['payload_hex'];
                $output= $nexelecDecoder->decodeUplink($input);


                $dbdata = array(
                    'deviceId' => $request_data['DevEUI'],
                    'type' => $output['data']['typeOfProduct'],
                    'tag' => '',
                    'name' => '',
                    'unit' => $output['data']['temperature']['unit'],
                    'value' => $output['data']['temperature']['value'][0] ?? '',
                    'strValue' => $output['data']['temperature']['value'][0] ?? '',
                    'fport' => $request_data['FPort'],
                    'message_time' => $request_data['Time'],
                );


                $sensor = Sensor::updateOrCreate(
                    ['deviceId' => $request_data['DevEUI'] ], $dbdata
                );
                $log = SensorLog::where('sensor_id', $sensor->id)->first();

                $log_data = array(
                    'sensor_id' => $sensor->id,
                );
                $values = $output['data']['temperature']['value'];
                $logs = [];
                $time = time();
                // Limit the iteration to 10 values maximum
                foreach (array_slice($values, 0, 10) as $i => $value) {
                    $timestamp = $time - (1800 * $i);
                    $logs[date("Y-m-d H:i:s", $timestamp)] = $value;
                }
                ksort($logs);
                $log_data['logs'] = json_encode($logs, JSON_PRETTY_PRINT);
                $log = SensorLog::updateOrCreate(
                    ['sensor_id' => $sensor->id,], $log_data
                );
            }
            
                foreach ($data as $key => $val) {
                    if ($key == 'externalTemperature2' && (is_array($val) || is_object($val)) ) {
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
            Log::debug("Lora data error " . $request_data['DevEUI']);
            Log::debug($e->getMessage());
            Log::debug(json_encode($data));
            return response()->json([
                'error' => $e->getMessage()
            ], 403);
        }

        return response()->json([
            'success' => "Received Data"
        ], 200);
    }
    public function zenner() {
        $sensors = Sensor::where('deviceId', 'LIKE', '04B6480C01%')->get();
        $areas = Area::all();
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
                    $sensor->area_name = $area->name;
                }
            }
            $sensor->logs;
        }
        return view('admin.zenner.index', [
            'sensors' => $sensors,
            'areas' => $areas
        ]);
    }
    public function nexelec() {
        $sensors = Sensor::where('deviceId', 'LIKE', '70B3D540F658D536%')->get();
        $areas = Area::all();
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
                    $sensor->area_name = $area->name;
                }
            }
            $sensor->logs;
        }
        return view('admin.nexelec.index', [
            'sensors' => $sensors,
            'areas' => $areas
        ]);
    }
}