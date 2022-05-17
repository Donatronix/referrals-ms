<?php

    /**
     * Tools for encrypt / decrypt test
     */
    $router->post('tools/data-encrypt', '\App\Http\Controllers\ToolsController@dataEncrypt');
    $router->post('tools/data-decrypt', '\App\Http\Controllers\ToolsController@dataDecrypt');
