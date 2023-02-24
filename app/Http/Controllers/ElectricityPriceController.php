<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\ElectricityPriceForecastTrait;
use DateTime;
use DateTimeZone;

class ElectricityPriceController extends Controller
{
    //
    use ElectricityPriceForecastTrait;
    public function index()
    {
        $forecast_data = $this->getElectricityPriceData();
        return view('admin.electricity_price.index', compact('forecast_data'));
    }
    public function getElectricityPricePointData()
    {
        try {
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
                $point_value = array_filter($forecast_data, function ($item) {
                    global $timestamp;
                    if ($item->time == $timestamp) {
                        return $item->value;
                    }

                });
                $points_data[] = [
                    "id" => $label,
                    "value" => $point_value
                ];

            }

            return $points_data;
        } catch (\Exception $e) {
            return $e->getMessage();
        }


    }
}
