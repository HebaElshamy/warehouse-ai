<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ScanSessionController;
use App\Http\Controllers\Api\SensorController;

Route::post('/sensor-reading', [SensorController::class, 'store']);

Route::post('/scan-session', [ScanSessionController::class, 'store']);
