<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tld\TldController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('campaigns/{campaign_id}')->group(
        function () {
            Route::apiResource('tlds', TldController::class);
        }
    );
});
