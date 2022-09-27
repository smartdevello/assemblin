<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\ElectricyPriceForcastTrait;
class ElectricityPriceController extends Controller
{
    //
    use ElectricyPriceForcastTrait;
    public function index()
    {
        $forecast_data = $this->getElectricityPriceData();
        return view('admin.electricity_price.index', compact('forecast_data'));
    }
}
