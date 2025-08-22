<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TravelRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    Route::middleware('jwt.auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('travel-requests', TravelRequestController::class)->except(['update', 'destroy']);
    Route::patch('travel-requests/{travelRequest}/status', [TravelRequestController::class, 'updateStatus']);
});


Route::get('health', function () {
    return response()->json([
        'status' => 'API is running',
        'timestamp' => now(),
        'environment' => app()->environment()
    ]);
});