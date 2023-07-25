<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::prefix('auth')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login')->middleware('throttle:3,1');
        Route::post('/forgot-password', 'forgotPassword')->middleware('throttle:3,1');
        Route::post('/reset-password', 'resetPassword')->middleware('throttle:3,1');

        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/user', 'user');
            Route::post('/logout', 'logout');
        });
    });
});
