<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SensorController;

Route::post('/sensor', [SensorController::class, 'store']);
Route::get('/pump/{kode_node}', [SensorController::class, 'pumpStatus']);
Route::post('/pump/toggle', [SensorController::class, 'togglePump']);
