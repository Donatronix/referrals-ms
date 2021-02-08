<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class ReferralBonusListener
{
    /**
     * Handle the event.
     *
     * @param
     * @return void
     */
    public function handle($data)
    {
        // Update referral status
        try {
            $user = User::where('user_id', $data['user_id']);
            $user->status = $data['status'];
            $user->save();
        } catch (\Throwable $e) {
            throw new \Exception('Can\'t update referral status');
        }
    }
}
