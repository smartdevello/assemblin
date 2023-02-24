<?php

namespace App\Http\Controllers;

use App\Http\Traits\ElectricityPriceForecastTrait;


class ElectricityPriceController extends Controller
{
    //
    use ElectricityPriceForecastTrait;
    public function index()
    {
        $forecast_data = $this->getElectricityPriceData();
        return view('admin.electricity_price.index', compact('forecast_data'));
    }
}
