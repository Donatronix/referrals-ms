<?php

namespace Database\Factories;

use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReferralCodeFactory extends Factory
{
    protected $model = ReferralCode::class;

    /**
     * @inheritDoc
     */
    public function definition(): array
    {
        return [
            'application_id' => str_replace('-', '.', $this->faker->slug(3, false)),
            'user_id' => User::all()->random()->id,
            'link' => $this->faker->word(),
            'is_default' => $this->faker->boolean(),
            'note' => $this->faker->text(255),
        ];
    }
}
