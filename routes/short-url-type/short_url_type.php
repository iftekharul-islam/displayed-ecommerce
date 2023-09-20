<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShortUrlType\ShortUrlTypeController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('short-url-types/all', [ShortUrlTypeController::class, 'all']);
    Route::apiResource('short-url-types', ShortUrlTypeController::class);
});
