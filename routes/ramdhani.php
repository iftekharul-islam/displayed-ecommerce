<?php

use App\Http\Controllers\Admin\Addons\RamdhaniController;
use App\Http\Controllers\Admin\Addons\ShippingClassController;
use App\Http\Controllers\Admin\CommonController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath','isInstalled']
    ], function () {
    Route::middleware(['adminCheck', 'loginCheck', 'XSS'])->prefix('admin')->group(function () {
        //shipping-classes
        Route::resource('shipping-classes', ShippingClassController::class)->except('destroy');
        Route::delete('delete/shipping-class/{id}', [CommonController::class, 'delete'])->name('admin.shipping-class.delete');
        Route::get('class-city/edit/{id}', [ShippingClassController::class, 'classCityEdit'])->name('class.city.edit');
        Route::put('class-city/update', [ShippingClassController::class, 'classCityUpdate'])->name('class.city.update');
        Route::put('shipping-class-status-change', [ShippingClassController::class, 'statusChange'])->name('shipping-class.status.change');
        Route::put('city-cod-change', [RamdhaniController::class, 'codStatusChange'])->name('cod.status.change');
    });
    Route::get('cities-by-shipping-classes/{id}', [RamdhaniController::class, 'getCities'])->name('shipping-classes.cities');
    Route::post('checkout/shipped-city', [RamdhaniController::class, 'saveShippedCity'])->name('save.shipped.city');
});
