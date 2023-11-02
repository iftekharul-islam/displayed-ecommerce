<?php

use App\Http\Controllers\RedirectionTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('redirection-type', [RedirectionTypeController::class, 'index']);
    Route::post('redirection-type', [RedirectionTypeController::class, 'update']);
    Route::get('redirection-type-options', [RedirectionTypeController::class, 'options']);
});
