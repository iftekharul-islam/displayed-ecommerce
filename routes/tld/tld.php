<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tld\TldController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('tlds/import', [TldController::class, 'import']);
    Route::apiResource('tlds', TldController::class);
});
