<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Role\RoleController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('roles')->controller(RoleController::class)->group(function () {
        Route::get('/all', 'all');
        Route::match(['PUT', 'PATCH'], '/{role_id}/permission/update', 'updatePermissions');
        // this route is for copy role permissions to another role
        // frontend not ready yet that's why it's commented
        // Route::post('/copy',  'copy');
    });
    Route::apiResource('roles', RoleController::class);
});
