<?php


use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\Addons\ErstopupController;

Route::prefix('v100')->group(function() {
    Route::prefix('ers')->group(function() {

        Route::middleware(['jwt.verify'])->group(function (){
        Route::get('submit-topup', [ErstopupController::class, 'submitTopup']);
        Route::match(['post','get'],'complete-order', [ErstopupController::class, 'completeOrder']);


        Route::get('reload-status', [ErstopupController::class, 'reloadStatus']);
        
        Route::get('history', [ErstopupController::class, 'history']);

        Route::get('telecom-list', [ErstopupController::class, 'telecomList']);
        Route::get('topup-options', [ErstopupController::class, 'topupOptions']);

        });
    });
});