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
        $user = User::where('id', $user->id)->first();

        if ($user->isEmpty() && !$referral->isEmpty()) {
            DB::transaction(function () use ($user, $referral) {
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
                    $reward = $referrerTotal->reward;
                    $referrerTotal->increment('amount');
                    $referrerTotal->increment('reward', User::REFERRER_POINTS);
                    $referrerTotal->update([
                        'twenty_four_hour_percentage' => ($referrerTotal->reward - $reward) * 100 / $referrerTotal->reward,
                    ]);

                })->publish('AddCoinsToBalanceInWallet', [
                    'reward' => User::REFERRER_POINTS,
                    'user_id' => $referral->user_id,
                ], config('pubsub.queue.crypto_wallets'));
            });
        }
    }
}
