<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvitedReferralResponseListener
{
    /**
     * Handle the event.
     *
     * @param
     * @return void
     */
    public function handle($data)
    {
        Log::info($data);
    }
}
