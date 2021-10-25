<?php

/*-------------------------
   T E S T S  Routes
-------------------------- */

Route::group(
    [
        'prefix' => 'tests'
    ],
    function ($router) {
        $router->get('db-test', function () {
            if (DB::connection()->getDatabaseName()) {
                echo "Connected successfully to database: " . DB::connection()->getDatabaseName();
            }
        });

        $router->get('referrals', '\App\Http\Controllers\PagesController@index');

        /**
         * Tools for encrypt / decrypt test
         */
        $router->post('tools/data-encrypt', '\App\Http\Controllers\ToolsController@dataEncrypt');
        $router->post('tools/data-decrypt', '\App\Http\Controllers\ToolsController@dataDecrypt');
    }
);
