<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportReport\ExportReportController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('export-reports')
        ->controller(ExportReportController::class)
        ->group(function () {
            Route::get('/', 'index');
            Route::get('/unread-count', 'unreadCount');
            Route::match(['PUT', 'PATCH'], '/{export_report}/mark-as-read', 'markAsRead');
            Route::delete('/{export_report}', 'destroy');
        });
});
