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

/**
 * @var \Laravel\Lumen\Routing\Router $router
 */

Route::get('/', function () use ($router) {
    return $router->app->version();
});

Route::get(env('API_PREFIX') . '/v1/db-test', function () {
    if (DB::connection()->getDatabaseName()) {
        echo "Connected successfully to database: " . DB::connection()->getDatabaseName();
    }
});



Route::get(env('API_PREFIX') . '/v1/decrypt-android', function () {

    $data = 'eyJpdiI6ImdaMStqQklVdlE4eFpQUVNSQWk3NndcdTAwM2RcdTAwM2RcbiIsIm1hYyI6Ik9kcEt0
Mkg0ekU2UTJrU0Vyc3ZwM0RnQ1ZPdm1YMk9mWjNndVJMZDZoYzhcdTAwM2RcbiIsInZhbHVlcyI6
IjdKYjg3aEpRaHhySFo2SmIrQ1pNanFmcEZPQmdwM1lmd21EREd4ZjdKWGt6OHluWFE2RDdhZ1ZD
WUV3NzkyN1NVK3dnNXJGb0crVzJcbld4L3ZTMUwrR1ErTlFPRFVWV0pEb2lDdDgrRVBodTV0cE5r
aWpCTXgvenVIbWxiRWNqbFh1NDQwZzBXUCtsM3NNNCt4MXNBWXZCMG9cbmd5b1RwTHhjN0gyVFlT
TGp0UmNUckhZVGo1RDE3ZmZaeTJCN0toTThKSWNYNzhkd01rQTJzMXRoRVRrQ1ZkMEprZkZhRngy
SVUrRkpcblRsRE13MjFwQU1ITWE4SEIzeUtuYlJXeEZJYitucnliS3lVTEZDSDlwV2dyU3ZORUNH
MHZsR2R0L25VY1VBalArdGdsN2xrTVh0aFpcbnc5MmZLcWZqOEpYc2FucTU3enV3L3RmQ2tsam05
RDdXZXk2ZjVydi9keWw0N0pHd1g3emw5Q1FOQ2thUzljYXYvU1lJUFY0aEVLTHZcbm1hTllhTUpr
V2Q2Y3ptdGw1N1I3cWZLODJZZmNud3diMHUxYU1FR0t2VElRZUdRXHUwMDNkXG4ifQ==';

    dd(Illuminate\Support\Facades\Crypt::decrypt($data));
});



Route::get(env('API_PREFIX') . '/v1/decrypt-laravel', function () {

    /*
    $key = base64_decode(config('app.key'));

    $hash = hash('md5', random_bytes(32));

    dd($key, $hash);
*/

    $data = 'Qn7krdZXmk57wGjqRp7/t08tdWqNPfCf1cbBtAi2TQuMxxlOWcFLWHBK+t5Y80y7sNvfSQtLtR3W
LC9Be4LNWEKhYBRXkTrboFhbtEOQ3mktOtB3Cu0aW6bK3LFZmx/UhXCCyDKEWsIkn+pNLEBHqt9M
EflEcIx8D8B1KCAfP7j1q1ixtRrwaELhXJReyhupijWbaJlu3KxJh1MI/PEzkOxfJG1oGLk73zjG
2LZlBr3mspCFtNJqQZHgYllJNV+IFSCincnzUhv6FRIF6NXYS17EMo42j8it+hIXfuWbvNS+sxVh
J6tUExrrhLLZu7DCYQiAyBaB2OWjf7YGMpLfqvbZwIqixpqO6eKbWVsmm3PNrycMKISWkAeqip0Q
JIZjcEL2jN2XO44Xnyg81hw6o4rU/ls1+bmiAuUX4t3gjWo=';

    dd(App\Services\Crypt::decrypt($data));

    dd(Illuminate\Support\Facades\Crypt::decrypt($data));
});

Route::get(env('API_PREFIX') . '/v1/encrypt-test', function () {
    $data = json_encode([
        'androidId' => 'ee4d70c80cdac614',
        'applicationID' => 'net.sumra.wallet',
        'deviceBootloader' => 'unknown',
        'deviceBrand' => 'google',
        'deviceManufactured' => 'Google',
        'deviceModel' => 'Android SDK built for x86',
        'deviceSerialNumber' => 'EMULATOR30X0X12X0',
        'packageName' => 'net.sumra.wallet',
        'versionCode' => '1',
        'versionName' => '0.0.245'
    ]);

    dd(App\Services\Crypt::encrypt($data));

    // echo Illuminate\Support\Facades\Crypt::encrypt($data);
});

Route::group(
    ['prefix' => env('API_PREFIX') . '/v1'],
    function ($router) {
        include base_path('app/Api/V1/routes.php');
    }
);
