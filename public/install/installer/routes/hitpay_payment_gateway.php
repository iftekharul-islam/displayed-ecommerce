<?php

use App\Http\Controllers\Admin\Addons\HitpayPaymentGatewayController;
use Illuminate\Support\Facades\Route;

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::middleware(['XSS','isInstalled'])->group(function () {
    Route::group(
        [
            'prefix' => LaravelLocalization::setLocale(),
            'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath','isInstalled']
        ], function () {

            Route::get('hitpay/redirect', [HitpayPaymentGatewayController::class, 'hitpayRedirect']);
            Route::get('hitpay/redirect/wallet', [HitpayPaymentGatewayController::class, 'hitpayWalletRedirect']);

    });
});
