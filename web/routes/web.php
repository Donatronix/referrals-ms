<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group([
    'prefix' => env('APP_API_PREFIX', '')
], function ($router) {
    include base_path('app/Api/V1/routes.php');
});

/*-------------------------
   T E S T S  Routes
-------------------------- */
$router->group([
    'prefix' => env('APP_API_PREFIX', '') . '/tests'
], function ($router) {
    $router->get('db-test', function () {
        if (\DB::connection()->getDatabaseName()) {
            echo "Connected successfully to database: " . \DB::connection()->getDatabaseName();
        }
    });

    $router->get('pubsub', function (){
//        PubSub::publish('JoinNewUserRequest', [
//            'user_id' => '169daa87-ba27-4c23-88c1-ec5429cd5156',
//            'name' => 'Jhon smith',
//            'username' => 'dhanaprofit',
//            'phone' => '+380971819100',
//            'country' => 'nigeria',
//            'application_id' => '121345678910',
//            // 'referral_code' => 'GH455FGGG',
//            'custom_code' => '5850754GQW',
//            'type' => 'partner'
//        ], config('pubsub.queue.referrals'));

        PubSub::publish('JoinNewUserRequest', [
            'user_id' => '6522388a-f9ee-463e-a393-4470751eec77',
            'name' => 'rikton',
            'username' => 'rikton',
            'phone' => 380952011990,
            'country' => NULL,
            'type' => 'client',
            'application_id' => 'V14567890123',
            'referral_code' => '23KS83-G9QEKD',
        ], config('pubsub.queue.referrals'));
    });
});
