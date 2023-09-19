<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcludedDomain\ExcludedDomainController;

Route::get('excluded-domains/export/download/{code}', [ExcludedDomainController::class, 'exportDownload']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('excluded-domains/export', [ExcludedDomainController::class, 'export']);
    Route::apiResource('excluded-domains', ExcludedDomainController::class);
});
