<?php

namespace Database\Factories;

use App\Models\Total;
use Illuminate\Database\Eloquent\Factories\Factory;

class TotalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Total::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'user_id' => function() {
                return \App\Models\User::all()->random()->id;
            },
            'amount' => rand(1, 500),
            'reward' => rand(1, 500),
        ];
    }
}
