<?php


namespace Database\Factories;

use App\Models\ReferralCode;
use App\Models\User;
use App\Services\Firebase;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReferralCodeFactory extends Factory
{
    protected $model = ReferralCode::class;
    /**
     * @inheritDoc
     */
    public function definition()
    {
        $user = User::all()->random();
        //$app = Application::where('user_id', $user)->get();

        return [
            'package_name' => str_replace('-', '.', $this->faker->slug(3, false)),
            'user_id' => User::all()->random()->id,
          //  'referral_link' => Firebase::linkGenerate($user->referral_code, $app->package_name),
            'referral_link' => uniqid(),
            'code' => ''
        ];
    }
}
