<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tld\TldController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('campaigns/{campaign}')->group(
        function () {
            Route::get('tlds/get', [TldController::class, 'get']);
            Route::apiResource('tlds', TldController::class);
        }
    );
});
