<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return DB::connection('choto_analytics_db')
        ->table('analytics')
        ->orderBy('id', 'asc')
        ->take(10)
        ->get();
});

// auth routes
require __DIR__ . '/auth/auth.php';

// dashboard routes
require __DIR__ . '/dashboard/dashboard.php';

// role routes
require __DIR__ . '/role/role.php';

// role modules
require __DIR__ . '/module/module.php';

// user routes
require __DIR__ . '/user/user.php';

// tld routes
require __DIR__ . '/tld/tld.php';

// campaign routes
require __DIR__ . '/campaign/campaign.php';

// short-url routes
require __DIR__ . '/short-url/short_url.php';

// excluded-domain routes
require __DIR__ . '/excluded-domain/excluded_domain.php';
