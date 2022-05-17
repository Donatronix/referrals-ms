<?php

/**
 * @var Laravel\Lumen\Routing\Router $router
 */
$router->group([
    'prefix' => env('APP_API_VERSION', ''),
    'namespace' => '\App\Api\V1\Controllers',
], function ($router) {
    /**
     * Internal access
     */
    $router->group([
        'middleware' => 'checkUser',
    ], function ($router) {
        /**
         * Referrals
         */
        $router->group([
            'prefix' => 'referrals',
        ], function ($router) {
            $router->get('/', 'ReferralController@index');
            $router->post('/', 'ReferralController@create');
        });

        /**
         *  Referral code
         */
        $router->group([
            'prefix' => 'referral-codes',
        ], function ($router) {
            $router->get('/', 'ReferralCodeController@index');
            $router->get('/user', 'ReferralCodeController@getDataByUserId');
            $router->post('/', 'ReferralCodeController@store');
            $router->get('/{id}', 'ReferralCodeController@show');
            $router->put('/{id}', 'ReferralCodeController@update');
            $router->delete('/{id}', 'ReferralCodeController@destroy');
            $router->put('/{id}/default', 'ReferralCodeController@setDefault');
        });

        /**
         * Leaderboard
         */
        $router->get('leaderboard', 'LeaderboardController@index');
        $router->post('check-totals', 'LeaderboardController@checkRemoteServices');
    });

    /**
     * ADMIN PANEL
     */
    $router->group([
        'prefix' => 'admin',
        'namespace' => 'Admin',
        'middleware' => [
            'checkUser',
            'checkAdmin',
        ],
    ], function ($router) {
        /**
         * Referrals
         */
        $router->get('referrals-list', 'UsersController@index');
        $router->get('referrals-list/{id:[\d]+}', 'UsersController@show');

        /**
         * Referrals
         */
        $router->get('transactions', 'TransactionsController@index');
        $router->get('transactions/{id}', 'TransactionsController@show');
        $router->post('transactions', 'TransactionsController@store');
        $router->patch('transactions/{id}', 'TransactionsController@update');
        $router->delete('transactions/{id}', 'TransactionsController@destroy');
    });
});
