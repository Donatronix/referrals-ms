<?php

/**
 * @var Laravel\Lumen\Routing\Router $router
 */

$router->group(
    ['prefix' => 'referral'],
    function($router){
        /**
         * Common
         */
        $router->get('/', '\App\Api\V1\Controllers\MainController@index');
        $router->post('/', '\App\Api\V1\Controllers\MainController@create');
        $router->get('invite', '\App\Api\V1\Controllers\MainController@invite');

        /**
         * Management
         */
        $router->get('manager/validate/user', '\App\Api\V1\Controllers\ManagementController@validateUser');
        $router->get('manager/validate/referrer', '\App\Api\V1\Controllers\ManagementController@validateReferrer');

        /**
         * Analytics
         */
        $router->get('analytics/byLink', '\App\Api\V1\Controllers\AnalyticsController@index');
        $router->get('analytics/unregistered', '\App\Api\V1\Controllers\AnalyticsController@unregistered');
    }
);
