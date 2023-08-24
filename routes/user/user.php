<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('users')->controller(UserController::class)->group(function () {
        Route::get('/trashes',  'trashes');
        Route::match(['PUT', 'PATCH'], '/{user}/trashes',  'restore');
        Route::delete('/trashes',  'forceDeletes');
        Route::match(['PUT', 'PATCH'], '/update/profile', 'updateProfile');
    });
    Route::apiResource('users', UserController::class);
});
