<?php

namespace App\Http\Traits;

use SimpleXMLElement;

trait ElectricyPriceForcastTrait
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
}