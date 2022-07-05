<?php

/**
 * @var Laravel\Lumen\Routing\Router $router
 */
$router->group([
    'prefix' => env('APP_API_VERSION', ''),
    'namespace' => '\App\Api\V1\Controllers',
], function ($router) {
    /**
     * PUBLIC ACCESS
     */
    $router->group([
        'namespace' => 'Application',
    ], function ($router) {
        $router->get('/subscribers/leaderboard', 'LeaderboardController@index');
    });

    /**
     * USER APPLICATION PRIVATE ACCESS
     */
    $router->group([
        'middleware' => 'checkUser',
        'namespace' => 'Application',
    ], function ($router) {
        /**
         * Leaderboard
         */
        $router->get('leaderboard', 'LeaderboardController@index');
        //$router->post('check-totals', 'LeaderboardController@checkRemoteServices');
        $router->get('invited-users/{id}', 'LeaderboardController@show');

        /**
         * Referrals
         */
        $router->group([
            'prefix' => 'referrals',
        ], function ($router) {
            $router->get('/', 'ReferralController@index');
            $router->post('/', 'ReferralController@create');

            /**
             * Leaderboard
             */
            $router->get('/leaderboard', 'LeaderboardController@index');
            $router->post('/check-totals', 'LeaderboardController@checkRemoteServices');
            $router->get('/invited-users/{id}', 'LeaderboardController@show');
        });

        /**
         * Promo codes
         */
        $router->group([
            'prefix' => 'promo-codes',
        ], function ($router) {
            $router->get('/generate', 'PromoCodeController@getPromoCode');
            $router->post('/validate', 'PromoCodeController@validatePromoCode');
        });

        /**
         *  Referral code
         */
        $router->group([
            'prefix' => 'referral-codes',
        ], function ($router) {
            $router->get('/', 'ReferralCodeController@index');
            $router->get('/user', 'ReferralCodeController@getDataByUser');
            $router->post('/', 'ReferralCodeController@store');
            $router->get('/{id}', 'ReferralCodeController@show');
            $router->put('/{id}', 'ReferralCodeController@update');
            $router->delete('/{id}', 'ReferralCodeController@destroy');
            $router->put('/{id}/default', 'ReferralCodeController@setDefault');
        });

        $router->group([
            'prefix' => '',
        ], function ($router) {

            //Referral and code summary
            $router->get('/summary', 'SummaryController@index');
        });
    });

    /**
     * ADMIN PANEL ACCESS
     */
    $router->group([
        'prefix' => 'admin',
        'namespace' => 'Admin',
        'middleware' => [
            'checkUser',
            'checkAdmin',
        ],
    ], function ($router) {

        //Referral and code summary
        $router->get('/summary-listing', 'SummaryController@listing');

        /**
         * Leaderboard
         */
        $router->get('/leaderboard-listing', 'LeaderboardController@index');
        $router->get('/leaderboard-listing/invited-users/{id}', 'LeaderboardController@show');

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

        /**
         * Referrals total earnings
         */
        $router->get('wallets/total-earnings', 'ReferralController@getWalletTotal');
    });

    /**
     * MICROSERVICE DATA EXCHANGE ACCESS
     */
    $router->group([
//        'middleware' => 'checkMS',
        'namespace' => 'Webhooks',
    ], function ($router) {
        /**
         * Referrals total earnings
         */
        $router->get('total-earnings', 'ReferralController@getReferralTotals');
        $router->get('leaderboard/overview-earnings/{referrerId}', 'ReferralController@getPlatformEarnings');
    });
});
