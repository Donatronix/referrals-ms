<?php

use App\Models\Application;
use Faker\Generator as Faker;

$factory->define(Application::class, function (Faker $faker) {
    return [
        'package_name' => str_replace('-', '.', $faker->slug(3, false)),
        'device_id' => uniqid('', false),
        'device_name' => $faker->words(3),
        'ip' => $faker->ipv4(),
        'metadata' => '',

        'referrer_code' => '',
        'user_id' => '',

        'user_status' => '',
        'referrer_id' => '',
        'referrer_status' => '',
    ];
});
