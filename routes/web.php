<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShortUrl\ShortUrlController;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('vx/{code}', [ShortUrlController::class, 'sortUrlRedirection']);

Route::get('/test', function (Request $request) {
    $endpoint = "https://www.google-analytics.com/collect";

    // Build the data payload
    $data = [
        'v'   => 1,
        'tid' => 'G-94Z310XVW0', // Replace with your tracking ID
        'cid' => 'G-94Z310XVW0', // You need to generate a unique client ID for each user
        't'   => 'pageview',
        'dp'  => $request->path(), // Get the current path from the request
    ];

    // Make the HTTP request to Google Analytics
    $client = new Client();
    $client->post($endpoint, [
        'form_params' => $data,
    ]);

    return "Hello";
});

Route::get('/test2', function (Request $request) {
    return view('test');
});
