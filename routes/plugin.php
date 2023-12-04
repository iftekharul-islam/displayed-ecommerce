<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Plugin\PluginController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::middleware(['XSS', 'isInstalled'])->group(function () {
    Route::group(
        [
            'prefix' => LaravelLocalization::setLocale(),
            'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']
        ],
        function () {

            Route::middleware(['adminCheck', 'loginCheck'])->group(function () {

                //role
                Route::prefix('admin')->group(function () {
                    //dashboard
                    Route::get('installed-plugins', [PluginController::class, 'index'])->name('admin.plugin.index');
                    Route::get('plugins/activate/{slug}', [PluginController::class, 'activatePlugin'])->name('admin.plugin.activate');
                    Route::get('plugins/deactivate/{slug}', [PluginController::class, 'deactivatePlugin'])->name('admin.plugin.deactivate');
                });
            });
        }
    );
});
