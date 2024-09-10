<?php

namespace App\Http\Controllers;

use App\Models\DEOS_controller;
use App\Http\Traits\ElectricityPriceForecastTrait;
use App\Http\Traits\AssemblinInit;

class ElectricityPriceController extends Controller
{
    //
    use ElectricityPriceForecastTrait;
    use AssemblinInit;
    public function index()
    {
        $forecast_data = $this->getElectricityPriceData();
        return view('admin.electricity_price.index', compact('forecast_data'));
    }
    public function sendToDEOS()
    {
        $id= 42;
        $controller = DEOS_controller::where('id', $id)->first();
        if (!$controller) {
            return back()->with('error', 'Not found');
        }

        return $this->sendForcasttoDEOS('electricityprice_forecast', $controller->id);
    }
}
