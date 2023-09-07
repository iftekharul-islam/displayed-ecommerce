<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Notification\NotificationController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('notifications')
        ->controller(NotificationController::class)
        ->group(function () {
            Route::get('/', 'index');
            Route::delete('/{notification}', 'destroy');
        });
});
