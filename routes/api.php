<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::put('/users/{user}', [UserController::class, 'update']);
});
