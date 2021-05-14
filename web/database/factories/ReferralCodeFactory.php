<?php


namespace Database\Factories;

use App\Models\ReferalCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReferralCodeFactory extends Factory
{

    /**
     * @inheritDoc
     */
    public function definition()
    {
        return [
            'package_name' => str_replace('-', '.', $this->faker->slug(3, false)),
            'user_id' => 0,
            'referral_link' => '',
            'code' => ''
        ];
    }
}
