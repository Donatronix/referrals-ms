<?php

namespace App\Listeners;

use App\Models\User;
use Exception;
use Throwable;

class ReferralBonusListener
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
        // Update referral status
        try {
            $user = User::find($data['user_id']);
            $user->status = $data['status'];
            $user->save();
        } catch (Throwable $e) {
            throw new Exception('Can\'t update referral status');
        }
    }
}
