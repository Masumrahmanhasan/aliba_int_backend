<?php

use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Frontend\HomeController;
use GuzzleHttp\Client;

Route::get('/dump', function() {
    $query = [
        'instanceKey' => '7367999f-de6f-4e88-9d36-1642cff1746b',
        'language' => 'en',
        // 'itemId' => 'abb-639225839350'
        'vendorId' => 'abb-b2b-1833723532'
    ];

    $client = new Client();
    $response = $client->request('GET', 'http://otapi.net/service-json/GetVendorInfo', ['query' => $query]);

    if ($response->getStatusCode() == 200) {
        $content = json_decode($response->getBody(), true);
        if (is_array($content)) {
          return getArrayKeyData($content, 'VendorInfo', []);
        }
      }
      return [];
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
