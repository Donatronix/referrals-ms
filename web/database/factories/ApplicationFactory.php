<?php

use App\Models\Application;
use Faker\Generator as Faker;

$factory->define(Application::class, function (Faker $faker) {
    return [
        'package_name' => str_replace('-', '.', $faker->slug(3, false)),
        'device_id' => uniqid('', false),
        'device_name' => $faker->words(3, true),

        'user_id' => \App\Models\User::all()->random()->id,
        'referrer_code' => '',

        'user_status' => Application::INSTALLED_NO,
        'referrer_id' => 0,
        'referrer_status' => Application::REFERRER_NO,

        'ip' => $faker->ipv4(),
        'metadata' => '',
    ];
});
