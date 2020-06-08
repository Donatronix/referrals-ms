<?php

namespace Sumra\PubSub;

use App;

class PubSubServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        App::singleton('PubSub', function(){
            return new PubSub();
        });
    }

    public function boot() {
        if (!class_exists('PubSub')) {
            class_alias('\Sumra\PubSub\Facades\PubSub', 'PubSub');
        }
        // TODO change after development
        $basePath = base_path('vendor/sumra/pubsub/database/migrations');
        $this->loadMigrationsFrom($basePath);
        //$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        /*$this->publishes([
            __DIR__ . '../config/pubsub.php' => config_path('pubsub.php'),
        ]);*/
        $this->mergeConfigFrom(
            __DIR__ . '/../config/pubsub.php', 'pubsub'
        );
    }
}
