<?php
use App\Http\Controllers\AreaController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DEOS_controllerController;
use App\Http\Controllers\DEOS_pointController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\LorawanController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TrendGroupController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

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


// Route::get('/test', function () {
//     return view('test');
// })->middleware(['auth'])->name('dashboard');


Route::get('', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');
Route::get('/lora', [LorawanController::class, 'index'])->middleware(['auth'])->name('lorawan');

// Route::get('/setting', '\App\Http\Controllers\DashboardController@setting_index')->middleware(['auth']);
Route::group(['prefix' => '/setting', 'middleware' => 'auth'], function ($router) {
    Route::get('', [SettingController::class, 'setting_index'])->name('setting_index');
    Route::post('update_device_interval', [SettingController::class, 'update_device_interval'])->name('update_device_interval');
    
    // Route::get('/{id}', [LocationController::class, 'show'])->name('location-detail');    
    // Route::post('update/{id}', [LocationController::class, 'update'])->name('location-update');
    // Route::post('delete/{id}', [LocationController::class, 'destroy'])->name('location-delete');
    // Route::post('delete_buildings/{id}', [LocationController::class, 'delete_buildings'])->name('location-delete_buildings');
});

Route::group(['prefix' => 'location', 'middleware' => 'auth'], function ($router) {
    Route::get('', [LocationController::class, 'index'])->name('locations');
    Route::get('/{id}', [LocationController::class, 'show'])->name('location-detail');
    Route::post('create', [LocationController::class, 'create'])->name('location-create');
    Route::post('update/{id}', [LocationController::class, 'update'])->name('location-update');
    Route::post('delete/{id}', [LocationController::class, 'destroy'])->name('location-delete');
    Route::post('delete_buildings/{id}', [LocationController::class, 'delete_buildings'])->name('location-delete_buildings');
});

Route::group(['prefix' => 'building', 'middleware' => 'auth'], function ($router) {
    Route::get('', [BuildingController::class, 'index'])->name('buildings');
    Route::get('/{id}', [BuildingController::class, 'show'])->name('building-detail');
    Route::post('create', [BuildingController::class, 'create'])->name('building-create');
    Route::post('update/{id}', [BuildingController::class, 'update'])->name('building-update');
    Route::post('delete/{id}', [BuildingController::class, 'destroy'])->name('building-delete');
    Route::post('/{id}/delete-areas', [BuildingController::class, 'deleteAreas'])->name('building-delete-areas');
    Route::post('/{id}/delete-controllers', [BuildingController::class, 'deleteDeosControllers'])->name('building-delete-controllers');
});

Route::group(['prefix' => 'area', 'middleware' => 'auth'], function ($router) {
    Route::get('', [AreaController::class, 'index'])->name('areas');
    Route::get('/{id}', [AreaController::class, 'show'])->name('area-detail');
    Route::post('create', [AreaController::class, 'create'])->name('area-create');
    Route::post('update/{id}', [AreaController::class, 'update'])->name('area-update');
    Route::post('delete/{id}', [AreaController::class, 'destroy'])->name('area-delete');
});

Route::group(['prefix' => 'controller', 'middleware' => 'auth'], function ($router) {
    Route::get('', [DEOS_controllerController::class, 'index'])->name('controllers');
    Route::get('/{id}', [DEOS_controllerController::class, 'show'])->name('controller-detail');
    Route::post('create', [DEOS_controllerController::class, 'create'])->name('controller-create');
    Route::post('update/{id}', [DEOS_controllerController::class, 'update'])->name('controller-update');
    Route::post('delete/{id}', [DEOS_controllerController::class, 'destroy'])->name('controller-delete');
    Route::post('/{id}/add-point', [DEOS_controllerController::class, 'createPoint'])->name('controller-add-point');
    Route::post('/{id}/remove-points', [DEOS_controllerController::class, 'deletePoints'])->name('controller-remove-points');
    Route::post('/{id}/import-points', [DEOS_controllerController::class, 'importPointsFromCsv'])->name('controller-import-points');
    Route::get('/{id}/export-points', [DEOS_controllerController::class, 'exportPointsFromCsv'])->name('controller-export-points');
});

Route::group(['prefix' => 'point', 'middleware' => 'auth'], function ($router) {
    Route::get('', [DEOS_pointController::class, 'index'])->name('points');
    Route::get('/{id}', [DEOS_pointController::class, 'show'])->name('point-detail');
    Route::post('create', [DEOS_pointController::class, 'create'])->name('point-create');
    Route::post('update/{id}', [DEOS_pointController::class, 'update'])->name('point-update');
    Route::post('delete/{id}', [DEOS_pointController::class, 'destroy'])->name('point-delete');
});

Route::group(['prefix' => 'trendgroup', 'middleware' => 'auth'], function ($router) {
    Route::get('', [TrendGroupController::class, 'index'])->name('groups');
    Route::get('/{id}', [TrendGroupController::class, 'show'])->name('group-detail');
    Route::post('create', [TrendGroupController::class, 'create'])->name('group-create');
    Route::post('update/{id}', [TrendGroupController::class, 'update'])->name('group-update');
    Route::post('delete/{id}', [TrendGroupController::class, 'destroy'])->name('group-delete');
});

Route::post('/tokens/create', function (Request $request) {
    $user = $request->user();    
    $access_token = $user->createToken($request->token_name);
    $plainToken = $access_token->plainTextToken;

    $token = PersonalAccessToken::where('id', $access_token->accessToken->id);
    $token->update([
        'plainTextToken' => $plainToken
    ]);
    return redirect()->route('setting_index');
});
Route::post('/tokens/remove', function (Request $request) {

    $items = [];
    foreach (json_decode($request->selected_tokens) as $key => $val) {
        if ($val != true) continue;
        $items[] = $key;
    }   
    return redirect()->route('setting_index', compact('success', $request->selected_tokens));
});

Route::middleware('auth:sanctum')->get('/test', function() {
    return 'Hello World';
});


Route::middleware(['cors', 'auth:sanctum'])->group(function(){

    Route::group(['prefix' => 'dashboard'], function ($router) {
        Route::post('update', [DashboardController::class, 'update'])->name('update_dashboard');
        Route::get('restartAsmServices', [DashboardController::class, 'restartAsmServices'])->name('restartAsmServices');        
    });
    
    Route::group(['prefix' => 'lorawan'], function ($router) {
        Route::post('receive_data', [LorawanController::class, 'receive_data'])->name('receive_data');
        Route::get('receive_csvfile', [LorawanController::class, 'receive_csvfile'])->name('receive_csvfile');
    });

    Route::group(['prefix' => 'point'], function ($router) {
        Route::get('getPoints', [PointController::class, 'getPoints'])->name('getPoints');
        Route::get('checkPoints', [PointController::class, 'checkPoints'])->name('checkPoints');
        
        Route::post('writePointstoLocalDB', [PointController::class, 'writePointstoLocalDB'])->name('writePointstoLocalDB');
        Route::post('writePointsbyid', [PointController::class, 'writePointsbyid'])->name('writePointsbyid');

        Route::get('readable', [PointController::class, 'getReadablePoints'])->name('getReadablePoints');
        Route::get('writable', [PointController::class, 'getWritablePoints'])->name('getWritablePoints');
        
    });    

    Route::group(['prefix' => 'trend'], function ($router) {
        Route::get('getTrends', [PointController::class, 'getTrends'])->name('getTrends');
        // Route::post('values',  [PointController::class, 'getTrendValues'])->name('getTrendValues');
    });

    Route::group(['prefix' => 'sensor'], function ($router) {
        Route::get('getSensors', [FoxeriotController::class, 'getSensors'])->name('getSensors');
        Route::post('updatePoints', [FoxeriotController::class, 'updateSensorsPoint'])->name('updateSensorsPoint');

    });

    Route::group(['prefix' => 'observation'], function ($router) {
        Route::get('getObservations', [FoxeriotController::class, 'getObservations'])->name('getObservations');      

    });

    Route::get('foxeriot/automatic_update', '\App\Http\Controllers\FoxeriotController@automatic_update');

    
    Route::group(['prefix' => 'asm_server/config'], function ($router) {
        Route::get('getSERVERConfig', [AsmServerController::class, 'getSERVERConfig'])->name('getSERVERConfig');
        Route::get('getRESTconfig', [AsmServerController::class, 'getRESTconfig'])->name('getRESTconfig');
    });

    
    Route::group(['prefix' => 'trendgroup'], function ($router) {  
        Route::post('/receive_csv', [TrendGroupController::class, 'receive_csv'])->name('receive_csv');
    });
});


require __DIR__.'/auth.php';
