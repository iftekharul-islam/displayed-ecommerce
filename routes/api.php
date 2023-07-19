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

// auth routes
require __DIR__ . '/auth/auth.php';

// role routes
require __DIR__ . '/role/role.php';

// role modules
require __DIR__ . '/module/module.php';

// user routes
require __DIR__ . '/user/user.php';

// campaign routes
require __DIR__ . '/campaign/campaign.php';
