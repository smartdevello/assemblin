<?php

use App\Http\Controllers\FoxeriotController;
use App\Http\Controllers\PointController;
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

    Route::get('points/readable', '\App\Http\Controllers\PointController@getReadablePoints');
    Route::get('points/writable', '\App\Http\Controllers\PointController@getWritablePoints');
    Route::put('points/writepointsbyid', '\App\Http\Controllers\PointController@writePointsbyid');
    Route::post('points/WritePointsfromLocal', '\App\Http\Controllers\PointController@WritePointsfromLocal');
    Route::get('points/trends', '\App\Http\Controllers\PointController@getTrendPoints');
    Route::post('points/trends/values', '\App\Http\Controllers\PointController@getTrendValues');


    Route::group(['prefix' => 'point'], function ($router) {
        Route::get('', [PointController::class, 'getPoints'])->name('getPoints');
        Route::post('writePointstoLocalDB', [PointController::class, 'writePointstoLocalDB'])->name('writePointstoLocalDB');
        Route::post('writePointsbyid', [PointController::class, 'writePointsbyid'])->name('writePointsbyid');
    });    



    Route::group(['prefix' => 'sensor'], function ($router) {
        Route::get('', [FoxeriotController::class, 'getSensors'])->name('getSensors');
        Route::post('updatePoints', [FoxeriotController::class, 'updateSensorsPoint'])->name('updateSensorsPoint');


    });
    Route::put('foxeriot/sensors/updatePoints', '\App\Http\Controllers\FoxeriotController@updateSensorsPoint');
    Route::get('foxeriot/observations', '\App\Http\Controllers\FoxeriotController@getObservations');
    Route::post('foxeriot/getDEOS_point_name', '\App\Http\Controllers\FoxeriotController@getDEOS_point_name');
    Route::get('foxeriot/automatic_update', '\App\Http\Controllers\FoxeriotController@automatic_update');

    

    Route::get('asm_server/config/getSERVERConfig', '\App\Http\Controllers\AsmServerController@getSERVERConfig');
    Route::get('asm_server/config/getRESTconfig', '\App\Http\Controllers\AsmServerController@getRESTconfig');

});
