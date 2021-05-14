<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/dashboard', function () {
    return view('welcome');
});


Route::get('/test', function () {
    return view('test');
});

Route::get('/', '\App\Http\Controllers\DashboardController@index');
Route::get('/location', '\App\Http\Controllers\LocationController@index');
Route::get('/building', '\App\Http\Controllers\BuildingController@index');
Route::get('/area', '\App\Http\Controllers\AreaController@index');
Route::get('/controller', '\App\Http\Controllers\DEOS_controllerController@index');
Route::get('/setting', '\App\Http\Controllers\DashboardController@setting_index');


