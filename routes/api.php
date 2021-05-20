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


    


    Route::group(['prefix' => 'point'], function ($router) {
        Route::get('', [PointController::class, 'getPoints'])->name('getPoints');

        Route::post('writePointstoLocalDB', [PointController::class, 'writePointstoLocalDB'])->name('writePointstoLocalDB');
        Route::post('writePointsbyid', [PointController::class, 'writePointsbyid'])->name('writePointsbyid');

        Route::get('readable', [PointController::class, 'getReadablePoints'])->name('getReadablePoints');
        Route::get('writable', [PointController::class, 'getWritablePoints'])->name('getWritablePoints');
        
    });    

    Route::group(['prefix' => 'trend'], function ($router) {
        Route::get('', [PointController::class, 'getTrends'])->name('getTrends');
        // Route::post('values',  [PointController::class, 'getTrendValues'])->name('getTrendValues');
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
