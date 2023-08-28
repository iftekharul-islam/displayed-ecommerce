<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\DashboardController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('/admin/dashboards')->controller(DashboardController::class)->group(function () {
        Route::get('campaigns/summaries',  'campaignsSummaries');
    });
});
