<?php

namespace App\Listeners;

use App\Services\ReferralService;
use Illuminate\Support\Facades\Log;

class JoinUserRequestListener
{
    /**
     * Handle the event.
     *
     * @param
     *
     * @return void
     */
    public function handle($data)
    {
        try {
            // Register the inviting new user in the referral program
            $newUser = ReferralService::getUser($data['new_user_id']);

            // Adding an inviter to a new user
            ReferralService::setInviter($newUser, $data['new_user_id']);
        } catch (\Exception $e){
            Log::error($e->getMessage());
        }
    }
}
