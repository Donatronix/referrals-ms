<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
*/

/**
 * @var \Laravel\Lumen\Routing\Router $router
 */

Route::get('/', function () use ($router) {
    return $router->app->version();
});

Route::get(env('API_PREFIX') . '/db-test', function () {
    if (DB::connection()->getDatabaseName()) {
        echo "Connected successfully to database: " . DB::connection()->getDatabaseName();
    }
});

Route::group(
    ['prefix' => env('API_PREFIX') . '/v1'],
    function ($router) {
        include base_path('app/Api/V1/routes.php');
    }
);
