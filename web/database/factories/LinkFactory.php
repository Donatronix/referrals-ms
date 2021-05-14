<?php

namespace Database\Factories;

use App\Models\Link;
use App\Services\Firebase;
use Illuminate\Database\Eloquent\Factories\Factory;

class LinkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Link::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'package_name' => str_replace('-', '.', $this->faker->slug(3, false)),
            'user_id' => 0,
            'referral_link' => ''
        ];
    }
}

