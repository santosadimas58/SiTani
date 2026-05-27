<?php

use App\Http\Controllers\Api\SensorController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [SensorController::class, 'health']);
Route::match(['get', 'post'], '/sensor', [SensorController::class, 'store']);
Route::get('/pump/{node}', [SensorController::class, 'pumpStatus']);
Route::post('/pump/toggle', [SensorController::class, 'togglePump']);
