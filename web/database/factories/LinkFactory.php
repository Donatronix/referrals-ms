<?php

use App\Models\Link;
use App\Services\Firebase;
use Faker\Generator as Faker;

$factory->define(Link::class, function (Faker $faker) {
    return [
        'package_name' => str_replace('-', '.', $faker->slug(3, false)),
        'user_id' => 0,
        'referral_link' => ''
    ];
});
