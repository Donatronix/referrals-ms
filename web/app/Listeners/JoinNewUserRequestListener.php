<?php

namespace App\Listeners;

use App\Models\ReferralCode;
use App\Models\Total;
use App\Models\User;
use App\Services\ReferralCodeService;
use App\Services\ReferralService;
use App\Traits\GetCountryTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PubSub;

class JoinNewUserRequestListener
{
    use GetCountryTrait;

    /**
     * Handle the event.
     *
     * @param array $data
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function handle(array $data): void
    {
        // Logging income data
        if(env('APP_DEBUG')){
            Log::info($data);
        }

        // Do validate input data
        $validation = Validator::make($data, [
            'user_id' => 'required|string|min:36|max:36',
            'name' => 'sometimes|string',
            'username' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'country' => 'sometimes|string',
            'application_id' => 'sometimes|string|min:10',
            'referral_code' => 'sometimes|string|min:8',
            'custom_code' => 'sometimes|string|min:8',
            'type' => 'sometimes|string',
        ]);

        // If validation error, the stop
        if ($validation->fails()) {
            Log::error('Validation error: ' . $validation->errors());
            exit();
        }

        // Get validated input
        $inputData = (object)$validation->validated();

        // Register the new user in the referral program
        $newUser = ReferralService::getUser($inputData->user_id);

        // Find Referrer ID by its referral code and application ID
        $parent_user_id = config('settings.empty_uuid');
        if(isset($inputData->referral_code)){
            $parent_user_id = ReferralCode::query()
                ->select('user_id')
                ->byReferralCode($inputData->referral_code)
                ->pluck('user_id')
                ->first();
        }

        // Fill user data and save
        $newUser->fill([
            'referrer_id' => $parent_user_id,
            'name' => $inputData->name ?? null,
            'username' => $inputData->username ?? null,
            'country' => $inputData->country ?? $this->getCountry($inputData->phone),
            'type' => User::$types[$inputData->type]
        ]);
        $newUser->save();

        // If is referrer, the increase bonus and stats
        if($parent_user_id !== config('settings.empty_uuid')){
            $rewardAdd = 10; // 10 USD per each referral

            $total = Total::where('user_id', $parent_user_id)->first();

            if($total){
                $rewardBefore = $total->reward;
                $total->increment('amount');
                $total->increment('reward', $rewardAdd);
                $total->update([
                    'twenty_four_hour_percentage' => ($total->reward - $rewardBefore) * 100 / $total->reward,
                ]);
            }else{
                Total::create([
                    'user_id' => $parent_user_id,
                    'amount' => 1,
                    'reward' => $rewardAdd,
                    'twenty_four_hour_percentage' => 0
                ]);
            }

            if($inputData->type == User::TYPE_PARTNER){
                // influencer earns $10 commission
                PubSub::publish('EarnCommission', [
                    'user_id' => $parent_user_id,
                    'earning_type' => 'referrals',
                    'amount' => $rewardAdd,
                    'document_id' => null,
                ], config('pubsub.queue.g_met'));
            }else{
                // Send request to wallet for update balance
                PubSub::publish('UpdateBalanceRequest', [
                    'title' => 'Referral bonus for new user',
                    'posting' => 'increase',
                    'amount' => $rewardAdd,
                    'currency' => 'usd',
                    'type' => 'bonus',
                    'receiver_id' => $parent_user_id,
                    'document_id' => $total->id,
                    'document_object' => class_basename(get_class($total)),
                    'document_service' => env('RABBITMQ_EXCHANGE_NAME')
                ], config('pubsub.queue.crypto_wallets'));
            }
        }

        // If exist application_id, then set relation
        if(isset($inputData->application_id)) {
            DB::table('application_user')->insert([
                'user_id' => $newUser->id,
                'application_id' => $inputData->application_id ?? null,
            ]);
        }

        // If exist custom code from influencer, then try to create new referral code with link
        if(isset($inputData->custom_code)) {
            $request = new Request();
            $request->merge([
                'code' => $inputData->custom_code,
                'application_id' => $inputData->application_id ?? null,
                'note' => 'First influencer promo code'
            ]);

            ReferralCodeService::createReferralCode($request, $newUser);
        }

        // Adding an inviter to a new user
//        ReferralService::setInviter($newUser, $event['user_id']);
    }
}
