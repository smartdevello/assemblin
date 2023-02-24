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

        date_default_timezone_set('Europe/Helsinki');
        $today = new Datetime('today', new DateTimeZone('Europe/Helsinki'));
        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');


        $periodStart = date('YmdH00', $today->getTimestamp());
        $periodEnd = date('YmdH00', $tomorrow->getTimestamp());

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

                //Next Hour
                $now = new DateTime("now", new DateTimeZone('Europe/Helsinki'));
                $now->modify('+1 hours');
                $now->setTime($now->format("H"), 0, 0);
                $timestamp = $now->getTimestamp();

            } else if ($i == 25) {

                //current Hour
                $now = new DateTime("now", new DateTimeZone('Europe/Helsinki'));
                $now->setTime($now->format("H"), 0, 0);
                $timestamp = $now->getTimestamp();
            } else {
                $timestamp = $today->getTimestamp() + ($i - 1) * 3600;
            }
            $point_value = -1;

            foreach ($forecast_data as $item) {
                if ($item->time == $timestamp) {
                    $point_value = $item->value;
                }
            }

            $points_data[] = [
                "id" => $label,
                "value" => $point_value
            ];

        }

        return $points_data;
    }
}
