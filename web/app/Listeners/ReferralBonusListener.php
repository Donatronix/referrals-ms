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
            $link = User::find($data['sumra_user_id']);
            $link->status = $data['status'];
            $link->save();
        } catch (\Throwable $e) {
            throw new \Exception('Can\'t update referral status');
        }
    }
}
