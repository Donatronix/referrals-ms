<?php

use Faker\Generator as Faker;
use App\Models\ApplicationKey;

$factory->define(ApplicationKey::class, function (Faker $faker) {
    $cipherList = [
      'AES-128-ECB',
      'AES-256-ECB'
    ];

    $cipher = $faker->randomElement($cipherList);

    return [
        'version_key' => $faker->unique()->randomNumber(9),
        'cipher' => $cipher,
        'cipher_key' => $cipher === 'AES-128-ECB' ? hash('md5', $faker->text) : hash('sha256', $faker->text),
    ];
});
