<?php

use App\Http\Controllers\Module\ModuleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('modules', ModuleController::class);
});
