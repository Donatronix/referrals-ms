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

    /**
     * ADMIN PANEL
     */
    $router->group(
       [
       //    'middleware' => 'checkUser'
       ], // TODO: вернуть закомментированное
        function ($router) {
            /**
             *  Referral code
             */
            $router->get('referral-codes', 'ReferralCodeController@index');
            $router->get('referral-code-set/{id}', 'ReferralCodeController@setDefault');
            $router->post("referral-codes", 'ReferralCodeController@store');
            $router->get("referral-code/{id}", 'ReferralCodeController@show');
            $router->put('referral-codes/{id}', 'ReferralCodeController@update');
            $router->delete('referral-codes/{id}', 'ReferralCodeController@destroy');

            /*
             * Templates
             * */
            $router->get('/landingpage', 'LandingpageController@index');
            $router->post('/landingpage/{id:[\d+]}', 'LandingpageController@save');

            /**
             * Referral
             */
            $router->get('/', 'ReferralController@index');
            $router->post('inviting', 'ReferralController@inviting');

            /**
             * Management
             */
            $router->get('manager/validate/user', 'ManagementController@validateUser');
            $router->get('manager/validate/referrer', 'ManagementController@validateReferrer');

            /**
             * ADMIN PANEL
             */
            $router->group([
                'prefix' => 'admin',
                'namespace' => 'Admin',
                'middleware' => 'checkAdmin'
            ], function ($router) {
                /*
                 * Templates
                 * */
                $router->get('/template', 'TemplateController@index');
                $router->post('/template/{id:[\d*]}', 'TemplateController@save');

                /**
                 * Refferals
                 */
                $router->get('referrals-list', 'UsersController@index');
                $router->get('referrals-list/{id:[\d]+}', 'UsersController@show');
            });
        }
    );
});
