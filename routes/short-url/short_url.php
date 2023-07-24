<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShortUrl\ShortUrlController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('short-urls', ShortUrlController::class);
});
