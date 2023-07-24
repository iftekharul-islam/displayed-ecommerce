<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SortUrl\ShortUrlController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('short-urls', ShortUrlController::class);
});
