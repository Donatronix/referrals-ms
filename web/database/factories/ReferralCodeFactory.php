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
        $user = User::whereNotNull('referrer_id')->inRandomOrder()->first();
        //  $app = Application::byOwner($user->id)->get();

        return [
            'id' => $this->faker->uuid,
            'user_id' => $user->referrer_id,
            'link' => 'http://' . $this->faker->lexify('????????'),
            //  'link' => Firebase::linkGenerate($user->referral_code, $app->application_id),
            'is_default' => $this->faker->boolean(),
            'note' => $this->faker->text(150),
            'application_id' => str_replace('-', '.', $this->faker->slug(3, false)),
        ];
    }
}
