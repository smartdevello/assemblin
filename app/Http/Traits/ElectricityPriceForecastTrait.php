<?php

namespace App\Http\Traits;

use SimpleXMLElement;
use DateTime;
use DateTimeZone;

trait ElectricityPriceForecastTrait
{
    public function getTimeZone()
    {
        $timezone = date_default_timezone_get() . ' => ' . date('e') . ' => ' . date('T');
        return $timezone;
    }
    public function getElectricityPriceData()
    {

        $curl = curl_init();
        $now = time() - 12 * 3600;
        $tomorrow = $now + 24 * 3600;

        $periodStart = date('YmdH00', $now);
        $periodEnd = date('YmdH00', $tomorrow);

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://web-api.tp.entsoe.eu/api?documentType=A44&in_Domain=10YFI-1--------U&out_Domain=10YFI-1--------U&periodStart=' . $periodStart . '&periodEnd=' . $periodEnd . '&securityToken=35db50f7-f48c-4d38-be46-1d79fa63fc9b',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $json = [];
        $xml = new SimpleXMLElement($response);
        $ns = $xml->getDocNamespaces();

        $series = $xml->children($ns[""]);
        $series = $xml->children($ns[""])->{'TimeSeries'};
        foreach ($series as $key => $member) {
            foreach ($member->{'Period'} as $period) {
                $start = $period->{'timeInterval'}->{'start'};
                $end = $period->{'timeInterval'}->{'end'};

                $time = strtotime($start);
                foreach ($period->{'Point'} as $point) {
                    $position = $point->{'position'};
                    $price = $point->{'price.amount'};
                    $json[] = [
                        'time' => $time,
                        'value' => (float) $price,
                    ];
                    $time = $time + 3600;
                }
            }
        }
        return $json;
    }
    public function getElectricityPricePointData()
    {

        $controller_id = 35;
        $forecast_data = $this->getElectricityPriceData();
        $today = new DateTime("today", new DateTimeZone('Europe/Helsinki'));
        $points_data = [];

        for ($i = 1; $i <= 26; $i++) {

            $label = sprintf('sahko.f01:I0%d', $i);
            $timestamp = "";
            if ($i == 26) {
                $date = new DateTime("now", new DateTimeZone('Europe/Helsinki'));
                $date->modify('+1 hours');
                $date->setTime($date->format("H"), 0, 0);
                $timestamp = $date->getTimestamp();

            } else if ($i == 25) {
                $date = new DateTime("now", new DateTimeZone('Europe/Helsinki'));
                $date->setTime($date->format("H"), 0, 0);
                $timestamp = $date->getTimestamp();
            } else {
                $timestamp = $today->getTimestamp() + ($i - 1) * 3600;
            }

            $item = array_filter($forecast_data, function ($item) {
                global $timestamp;
                if ($item->time == $timestamp)
                    return true;
            });
            $points_data[] = [
                "id" => $label,
                "value" => $item[0]->value
            ];

        }

        return $points_data;
    }
}
