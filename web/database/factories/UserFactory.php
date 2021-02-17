<?php

use Faker\Generator as Faker;
use App\Models\User;

$factory->define(User::class, function (Faker $faker) {
    return [
        'id' => $faker->unique()->numberBetween(1, 100),
        'user_name' => mb_strtolower($faker->firstName),
        'status' => 1
    ];
});
