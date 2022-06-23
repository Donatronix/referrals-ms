<?php

namespace Database\Factories;

use App\Models\Total;
use App\Models\User;
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
        $referrer = User::whereNotNull('referrer_id')->inRandomOrder()->first()->referrer_id;
        return [
            'id' => $this->faker->uuid,
            'user_id' => $referrer,
            'amount' => rand(1, 500),
            'reward' => function () use ($referrer) {
                return 3 * User::where('referrer_id', $referrer)->count();
            },
        ];
    }
}
