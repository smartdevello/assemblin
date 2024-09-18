<?php

namespace App\Http\Controllers;

use App\Models\DEOS_controller;
use App\Http\Traits\ElectricityPriceForecastTrait;
use App\Http\Traits\AssemblinInit;
use App\Models\HKA_Scheduled_JOb;
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
        $job = HKA_Scheduled_JOb::where('job_name', 'electricityprice_forecast')->first();
        $controller = DEOS_controller::where('id', $job->id)->first();
        if (!$controller) {
            return back()->with('error', 'Not found');
        }

        return $this->sendForcasttoDEOS('electricityprice_forecast', $controller->id);
    }
}
