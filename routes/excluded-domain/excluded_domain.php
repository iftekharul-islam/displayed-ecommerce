<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcludedDomain\ExcludedDomainController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('excluded-domain', ExcludedDomainController::class);
});
