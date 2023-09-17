<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportDownload\ReportDownloadController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('report-downloads')
        ->controller(ReportDownloadController::class)
        ->group(function () {
            Route::get('/', 'index');
            Route::get('/unread-reports', 'unreadReports');
            Route::get('/unread-count', 'unreadCount');
            Route::match(['PUT', 'PATCH'], '/{report_download}/mark-as-read', 'markAsRead');
            Route::delete('/{report_download}', 'destroy');
        });
});
