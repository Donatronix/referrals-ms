<?php

namespace Sumra\JsonApi;


use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Http\ResponseFactory;
use Sumra\JsonApi\Exceptions\Handler;

/**
 * Class JsonApiServiceProvider
 *
 * @package Sumra\JsonApi
 */
class JsonApiServiceProvider extends ServiceProvider
{
    public function register()
    {
       //
    }

    public function boot()
    {
        $this->app->bind(
            ExceptionHandler::class,
            Handler::class
        );

        /*$this->app->singleton(
            ExceptionHandler::class,
            Handler::class
        );*/

        ResponseFactory::macro('jsonApi', function ($data = null, $status = 200, $headers = [], $options = 0) {
            return new JsonApiResponse($data, $status, $headers, $options);
        });
    }
}
