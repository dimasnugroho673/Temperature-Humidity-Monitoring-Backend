<?php

use App\Http\Controllers\DeviceController;
use App\Http\Controllers\MonitoringController;
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

Route::get('/temperatures', [MonitoringController::class, 'index']);

Route::get('/temperature/latest', [MonitoringController::class, 'latest']);

Route::post('/temperature', [MonitoringController::class, 'store']);

Route::get('/temperature/store-get', [MonitoringController::class, 'storeWithGet']);

Route::post('/device', [DeviceController::class, 'store']);

Route::get('/config', [MonitoringController::class, 'config']);