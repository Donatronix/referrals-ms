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
        $newUser = collect($event['user']);

        $checkUser = User::where('id', $newUser->id)->first();

        if ($checkUser->isEmpty()) {
            $referrer_id = null;
            if(isset($event['referral_code'])){
                $referral = ReferralCode::where('code', $event['referral_code'])->first();
                $referrer_id = $referral->user_id;
            }

            // Create user
            User::query()->create([
                'id' => $newUser->id,
                'country' => $this->getCountry($newUser->phone_number),
                'referrer_id' => $referrer_id,
                'username' => $newUser->username ?? null,
                'name' => $newUser->name ?? null,
            ]);

            DB::table('application_user')->insert([
                'user_id' => $newUser->id,
                'application_id' => $event['application_id'] ?? null,
            ]);

            // If is referrer, the increase bonus and stats
            if($referrer_id){
                $referrerTotal = Total::where('user_id', $referrer_id)->first();
                $reward = $referrerTotal->reward;
                $referrerTotal->increment('amount');
                $referrerTotal->increment('reward', User::REFERRER_POINTS);
                $referrerTotal->update([
                    'twenty_four_hour_percentage' => ($referrerTotal->reward - $reward) * 100 / $referrerTotal->reward,
                ]);

                // Send request to wallet for update balance
                PubSub::publish('UpdateBalanceRequest', [
                    'title' => 'Referral bonus for new user',
                    'posting' => 'increase',
                    'amount' => User::REFERRER_POINTS,
                    'currency' => 'divit',
                    'type' => 'bonus',
                    'receiver_id' => $referrer_id,
                    'document_id' => $referrerTotal->id,
                    'document_object' => class_basename(get_class($referrerTotal)),
                    'document_service' => env('RABBITMQ_EXCHANGE_NAME')
                ], config('pubsub.queue.crypto_wallets'));
            }
        }
    }
}
