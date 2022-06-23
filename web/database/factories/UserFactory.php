<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => Str::orderedUuid(),
            'referrer_id' => function () {
                return $this->faker->boolean ? User::factory()->create()->id : null;
            },
            'country' => $this->faker->country(),
            'name' => $this->faker->name(),
            'username' => $this->faker->username(),
        ];
    }
}
