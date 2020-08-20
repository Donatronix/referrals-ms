<?php

/**
 * @var Laravel\Lumen\Routing\Router $router
 */

/* Referral's route */
$router->group([
    'namespace' => '\App\Api\V1\Controllers'
], function ($router) {
    $router->post('tools/data-encrypt', 'ToolsController@dataEncrypt');
    $router->post('tools/data-decrypt', 'ToolsController@dataDecrypt');

    $router->group([
        'prefix' => 'referrals'
    ], function ($router) {
        /**
         * Common
         */
        $router->get('/', 'MainController@index');
        $router->post('/', 'MainController@create');
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
            'namespace' => 'Admin'
        ], function ($router) {
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
        });
    });
});
