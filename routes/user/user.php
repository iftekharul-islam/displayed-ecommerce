<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('users')->controller(UserController::class)->group(function () {
        Route::match(['PUT', 'PATCH'], '/update/profile', 'updateProfile');
    });
    Route::apiResource('users', UserController::class);
});
