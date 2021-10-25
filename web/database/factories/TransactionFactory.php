<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'user_id' => function () {
                return User::all()->random()->id;
            },
            'user_plan' => $this->faker->randomElement(['basic', 'bronze', 'silver', 'gold']),
            'reward' => rand(1, 20),
            'currency' => $this->faker->currencyCode,
            'operation_name' => $this->faker->unique()->word,
        ];
    }
}
