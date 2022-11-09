<?php

use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Frontend\HomeController;
use GuzzleHttp\Client;

Route::get('/dump', function() {
    // $base_url = get_setting('mybd_api_url') . '/service-json/';
    // $instanceKey = get_setting('mybd_api_token');
    // dd($instanceKey);

    $query = [
        // 'instanceKey' => 'aa96861a-3c3d-49fb-bfa9-c4ec294c4fbf',
        // 'instanceKey' => '7367999f-de6f-4e88-9d36-1642cff1746b',
        'language' => 'en',
        'itemId' => '555582080064'
    ];

    $client = new Client();
    $response = $client->request('GET', 'http://otapi.net/service-json/GetItemFullInfoWithDeliveryCosts', ['query' => $query]);
    $contents = (string) $response->getBody();
    dd($contents);
});

/*
 * Global Routes
 * Routes that are used between both frontend and backend.
 */

// Switch between the included languages
Route::get('lang/{lang}', [LanguageController::class, 'swap']);

/*
 * Frontend Routes
 * Namespaces indicate folder structure
 */
Route::group(['namespace' => 'Frontend', 'as' => 'frontend.'], function () {
  include_route_files(__DIR__ . '/frontend/');
});

/*
 * Backend Routes
 * Namespaces indicate folder structure
 */
Route::group(['namespace' => 'Backend', 'prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'admin'], function () {
  /*
     * These routes need view-backend permission
     * (good if you want to allow more than one group in the backend,
     * then limit the backend features by different roles or permissions)
     *
     * Note: Administrator has all permissions so you do not have to specify the administrator role everywhere.
     * These routes can not be hit if the password is expired
     */
  include_route_files(__DIR__ . '/backend/');
});
