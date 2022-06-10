<?php

namespace App\Listeners;

use App\Models\ReferralCode;
use App\Models\Total;
use App\Models\User;
use App\Traits\GetCountryTrait;
use Illuminate\Support\Facades\DB;
use PubSub;

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
     * @param array $event
     *
     * @return void
     */
    public function handle(array $event): void
    {
        $user = collect($event['user']);
        $referralCode = $event['referralCode'];


        $referral = ReferralCode::query()->where('referralCode', $referralCode)->first();


        if (User::findOrFail($user->id)->isEmpty() && !empty($referral)) {
            PubSub::transaction(function () use ($user, $referral) {
                // Create order
                User::query()->create([
                    'id' => $user->id,
                    'country' => $this->getCountry($user->phone_number),
                    'referrer_id' => $referral->user_id,
                    'username' => $user->username ?? null,
                    'name' => $user->name ?? null,
                ]);

                DB::table('application_user')->insert([
                    'user_id' => $user->id,
                    'application_id' => $referral->application_id,
                ]);

                $referrerTotal = Total::where('user_id', $referral->user_id)->first();
                $referrerTotal->increment('amount');
                $referrerTotal->increment('reward', User::REFERRER_POINTS);
            })->publish('AddCoinsToBalance', [
                'reward' => User::REFERRER_POINTS,
                'user_id' => $referral->user_id,
            ], 'add_coins_to_balance');
        }


    }
}
