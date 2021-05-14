<?php

namespace Database\Factories;

use App\Models\Application;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class ApplicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Application::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'package_name' => str_replace('-', '.', $this->faker->slug(3, false)),
            'device_id' => uniqid('', false),
            'device_name' => $this->faker->words(3, true),

            'user_id' => User::all()->random()->id,
            'referrer_code' => '',

            'user_status' => Application::INSTALLED_NO,
            'referrer_id' => 0,
            'referrer_status' => Application::REFERRER_NO,

            'ip' => $this->faker->ipv4(),
            'metadata' => '',
        ];
    }
}
