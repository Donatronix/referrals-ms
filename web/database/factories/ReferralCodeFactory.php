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
            'application_id' => str_replace('-', '.', $this->faker->slug(3, false)),
            'user_id' => $user->id,
            'referral_link' => Firebase::linkGenerate($user->referral_code, 'net.sumra.chat'),
          //  'referral_link' => Firebase::linkGenerate($user->referral_code, $user->package_name),
            'code' => $user->referral_code,
            'is_default' => $this->faker->randomElement(['0', '1']),
            'application_id' => 'net.sumra.chat'
        ];
    }
}
