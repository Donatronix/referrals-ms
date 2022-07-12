<?php

/**
 * @var Laravel\Lumen\Routing\Router $router
 */
$router->group([
    'prefix' => env('APP_API_VERSION', ''),
    'namespace' => '\App\Api\V1\Controllers',
], function ($router) {
    /**
     * USER APPLICATION PRIVATE ACCESS
     */
    $router->group([
        'middleware' => 'checkUser',
    ], function ($router) {

        /**
         * Templates
         */
        $router->get('/landing-page', 'LandingPageController@index');
        $router->post('/landing-page', 'LandingPageController@store');
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
        /**
         * Templates
         */
        $router->get('/template', 'TemplateController@index');
        $router->post('/template', 'TemplateController@store');
    });
});
