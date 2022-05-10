<?php

namespace App\Listeners;

use App\Events\NewUserRegistered;
use App\Models\ReferralCode;
use App\Models\Total;
use App\Models\User;
use App\Traits\GetCountryTrait;
use Illuminate\Support\Facades\DB;

class NewUserRegisteredListener
{
    use GetCountryTrait;

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
    public function handle(mixed $event): void
    {
        $user = $event->user;
        $referralCode = $event->referralCode;


        $referral = ReferralCode::query()->where('referralCode', $referralCode)->first();


        //get country from phone number
        $id = $user->id;

        if (User::find($user->id)->isEmpty()) {

            User::query()->create([
                'id' => $id,
                'country' => $this->getCountry($user->phone_number),
                'referrer_id' => $referral->user_id,
                'username' => $user->username ?? null,
                'name' => $user->name ?? null,
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
}
