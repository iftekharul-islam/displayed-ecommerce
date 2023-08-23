<?php

use App\Http\Controllers\Campaign\CampaignController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('campaigns/trashes', [CampaignController::class, 'trashes']);
    Route::get('campaigns/trashes/{id}', [CampaignController::class, 'restore']);
    Route::delete('campaigns/trashes', [CampaignController::class, 'forceDeletes']);
    Route::get('campaigns/actives', [CampaignController::class, 'actives']);
    Route::apiResource('campaigns', CampaignController::class);
});
