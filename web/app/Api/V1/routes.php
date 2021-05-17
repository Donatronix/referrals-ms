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
    $router->post('/', 'MainController@create');

    /**
     * ADMIN PANEL
     */
    $router->group(
        ['middleware' => 'checkUser'],
        function ($router) {
            /**
             *  Referral code
             */
            $router->get('/referral-codes/', 'ReferralCodeController@index');
            $router->post("/referral-codes/", 'ReferralCodeController@store');
            $router->put('/referral-codes/update/{id}', 'ReferralCodeController@update');
            $router->delete('/referral-codes/delete/{id}', 'ReferralCodeController@delete');

            /*
             * Templates
             * */
            $router->get('/landingpage', 'LandingpageController@index');
            $router->post('/landingpage/{id:[\d+]}', 'LandingpageController@save');

            /*
             * Refcode
             * */
            $router->get('/refcode', 'RefcodeController@index');
            $router->post('/refcode', 'RefcodeController@generate');

            /**
             * Common
             */
            $router->get('/', 'MainController@index');
            $router->get('invite', 'MainController@invite');

            /**
             * Management
             */
            $router->get('manager/validate/user', 'ManagementController@validateUser');
            $router->get('manager/validate/referrer', 'ManagementController@validateReferrer');

            /**
             * Analytics
             */
            $router->get('analytics/byLink', 'AnalyticsController@index');
            $router->get('analytics/unregistered', 'AnalyticsController@unregistered');

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

                /**
                 * Applications
                 */
                $router->get('applications', 'ApplicationController@index');
                $router->get('application-keys', 'ApplicationKeyController@index');

                /**
                 * Devices
                 */
                $router->get('devices', 'DeviceController@index');

                /**
                 *
                 */
                $router->get('links', 'LinksController@index');
            });
        }
    );
});
