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

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

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


// test mail sending
Route::get('/test-mail', function () {
    $user = \App\Models\User::find(10);
    Mail::raw('Test Mail', function ($message) use ($user) {
        $message->to($user->email)
            ->subject('Test mail');
    });
    return 'mail sent';
});

// test notification sending

Route::get('/test-notification', function () {
    $user = \App\Models\User::find(10);
    $user->notify(new \App\Notifications\WelcomeNotification());
    return 'notification sent';
});
