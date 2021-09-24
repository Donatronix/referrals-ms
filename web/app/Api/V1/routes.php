<?php

/**
 * @var Laravel\Lumen\Routing\Router $router
 */

/* Referral's route */
$router->group([
    'prefix' => 'referrals',
    'namespace' => '\App\Api\V1\Controllers'
], function ($router) {
    // Register new user
    $router->post('/', 'ReferralController@create');

    $router->group(
        [
            'middleware' => 'checkUser'
        ],
        function ($router) {
            /**
             * Referral
             */
            $router->get('/', 'ReferralController@index');
            $router->post('inviting', 'ReferralController@inviting');

            /**
             *  Referral code
             */
            $router->get('referral-codes', 'ReferralCodeController@index');
            $router->get('referral-codes/user', 'ReferralCodeController@getDataByUserId');
            $router->post("referral-codes", 'ReferralCodeController@store');
            $router->get("referral-codes/{id}", 'ReferralCodeController@show');
            $router->put('referral-codes/{id}', 'ReferralCodeController@update');
            $router->delete('referral-codes/{id}', 'ReferralCodeController@destroy');
            $router->put('referral-codes/{id}/default', 'ReferralCodeController@setDefault');

            /**
             * Leaderboard
             */
            $router->get('leaderboard', 'LeaderboardController@index');
            $router->post('check-totals', 'LeaderboardController@checkRemoteServices');

            /**
             *  test
             */
            $router->get('test', 'TestController@test');

            /**
             * Templates
             */
            $router->get('/landing-page', 'LandingPageController@index');
            $router->post('/landing-page', 'LandingPageController@store');

            /**
             * ADMIN PANEL
             */
            $router->group([
                'prefix' => 'admin',
                'namespace' => 'Admin',
                'middleware' => 'checkAdmin'
            ], function ($router) {
                /**
                 * Referrals
                 */
                $router->get('referrals-list', 'UsersController@index');
                $router->get('referrals-list/{id:[\d]+}', 'UsersController@show');

                /**
                 * Templates
                 */
                $router->get('/template', 'TemplateController@index');
                $router->post('/template', 'TemplateController@store');
            });
        }
    );
});
