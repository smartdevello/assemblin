<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FoxeriotController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\LorawanController;
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

    Route::group(['prefix' => 'dashboard'], function ($router) {
        Route::post('update', [DashboardController::class, 'update'])->name('update_dashboard');
        Route::get('restartAsmServices', [DashboardController::class, 'restartAsmServices'])->name('restartAsmServices');        
    });
    
    Route::group(['prefix' => 'lorawan'], function ($router) {
        Route::post('receive_data', [LorawanController::class, 'receive_data'])->name('receive_data');
        Route::get('receive_csvfile', [LorawanController::class, 'receive_csvfile'])->name('receive_csvfile');
    });

    Route::group(['prefix' => 'point'], function ($router) {
        Route::get('', [PointController::class, 'getPoints'])->name('getPoints');
        Route::get('checkPoints', [PointController::class, 'checkPoints'])->name('checkPoints');
        
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

    Route::group(['prefix' => 'observation'], function ($router) {
        Route::get('', [FoxeriotController::class, 'getObservations'])->name('getObservations');      

    });

    Route::get('foxeriot/automatic_update', '\App\Http\Controllers\FoxeriotController@automatic_update');

    
    Route::group(['prefix' => 'asm_server/config'], function ($router) {
        Route::get('getSERVERConfig', [AsmServerController::class, 'getSERVERConfig'])->name('getSERVERConfig');
        Route::get('getRESTconfig', [AsmServerController::class, 'getRESTconfig'])->name('getRESTconfig');
    });    

});
