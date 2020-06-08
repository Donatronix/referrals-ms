<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/**
 * @var \Laravel\Lumen\Routing\Router $router
 */

Route::get('/', function () use ($router) {
    return $router->app->version();
});

Route::get('/api/v1/db-test', function () {
    if (DB::connection()->getDatabaseName()) {
        echo "Connected successfully to database: " . DB::connection()->getDatabaseName();
    }
});

Route::get('/api/v1/encrypt-test', function () {
    $data = [
        'package_name' => 'net.sumra.android',
        'device_id' => 're4rcereffdfdf',
        'device_name' => 'Xiomi 7A'
    ];

    echo Illuminate\Support\Facades\Crypt::encrypt($data);
});

Route::group(
    ['prefix' => 'api/v1'],
    function($router){
        include base_path('app/Api/V1/routes.php');
    }
);
