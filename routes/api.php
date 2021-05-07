<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware(['cors'])->group(function(){
    Route::get('points', '\App\Http\Controllers\PointController@getPoints');
    Route::get('points/readable', '\App\Http\Controllers\PointController@getReadablePoints');
    Route::get('points/writable', '\App\Http\Controllers\PointController@getWritablePoints');
    Route::put('points/writepointsbyid', '\App\Http\Controllers\PointController@WritePointsbyid');

    Route::get('points/trends', '\App\Http\Controllers\PointController@getTrendPoints');
    Route::post('points/trends/values', '\App\Http\Controllers\PointController@getTrendValues');

    Route::get('foxeriot/devices', '\App\Http\Controllers\FoxeriotController@getDevices');
    Route::get('foxeriot/observations', '\App\Http\Controllers\FoxeriotController@getObservations');
    Route::get('asm_server/config/', '\App\Http\Controllers\AsmServerController@getConfig');
    Route::get('asm_server/config/getRESTconfig', '\App\Http\Controllers\AsmServerController@getRESTconfig');

});
