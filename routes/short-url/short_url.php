<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShortUrl\ShortUrlController;

Route::get('short-urls/export/download/{code}', [ShortUrlController::class, 'download']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('short-urls/export', [ShortUrlController::class, 'export']);
    Route::post('short-urls/import', [ShortUrlController::class, 'import']);
    Route::apiResource('short-urls', ShortUrlController::class);
});
