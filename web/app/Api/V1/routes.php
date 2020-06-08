<?php

/**
 * @var Laravel\Lumen\Routing\Router $router
 */

$router->group(
    ['prefix' => 'referral'],
    function($router){
        $router->get('/', '\App\Api\V1\Controllers\MainController@index');
        $router->post('/', '\App\Api\V1\Controllers\MainController@create');
        $router->get('invite', '\App\Api\V1\Controllers\InviteController');
        $router->get('analytics/byLink', '\App\Api\V1\Controllers\AnalyticsController@index');
        $router->get('analytics/unregistered', '\App\Api\V1\Controllers\AnalyticsController@unregistered');
    }
);
