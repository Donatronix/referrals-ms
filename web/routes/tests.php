<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/*-------------------------
   T E S T S
-------------------------- */

$router->get('/tests/referrals/', '\App\Http\Controllers\PagesController@index');

$router->get('/tests/referrals/contacts/store', function () {
    return \App\Http\Controllers\TestController::viewMake("tests.contacts.store");
});

