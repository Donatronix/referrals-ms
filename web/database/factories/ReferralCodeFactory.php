<?php

namespace Database\Factories;

use App\Models\ReferralCode;
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
            'id' => $this->faker->uuid,
            'user_id' => function() {
                return \App\Models\User::all()->random()->id;
            },
            'link' => 'http://' . $this->faker->lexify('????????') ,
            'is_default' => $this->faker->boolean(),
            'note' => $this->faker->text(255),
            'application_id' => str_replace('-', '.', $this->faker->slug(3, false)),
        ];
    }
}
