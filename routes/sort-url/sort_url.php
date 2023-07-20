<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SortUrl\SortUrlController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('sort-urls', SortUrlController::class);
});
