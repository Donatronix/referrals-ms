<?php

namespace App\Listeners;

use App\Events\NewUserRegistered;
use App\Models\ReferralCode;
use App\Models\Total;
use App\Models\User;
use App\Traits\GetCountryTrait;
use App\Traits\TextToImageTrait;
use Illuminate\Support\Facades\DB;

class NewUserRegisteredListener
{
    use GetCountryTrait;
    use TextToImageTrait;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param NewUserRegistered $event
     *
     * @return void
     */
    public function handle(mixed $event)
    {
        $user = $event->user;
        $referralCode = $event->referralCode;


        $referral = ReferralCode::query()->where('referralCode', $referralCode)->first();


        //get country from phone number
        $id = $user->id;

        User::query()->create([
            'id' => $id,
            'country' => $this->getCountry($user->phone_number),
            'referrer_id' => $referral->user_id,
            'username' => $user->username,
            'name' => $user->name,
            'avatar' => $this->createImage(strtoupper(substr($user->name, 0, 1)))->showImage(),
        ]);

        DB::table('application_user')->insert([
            'user_id' => $id,
            'application_id' => $referral->application_id,
        ]);

        $referrerTotal = Total::query()->where('user_id', $referral->user_id)->first();
        $referrerTotal->increment('amount');
        $referrerTotal->increment('reward', User::REFERRER_POINTS);

    }
}
