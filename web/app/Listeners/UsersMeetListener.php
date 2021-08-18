<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;

class UsersMeetListener
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
        \App\Services\ReferralCodeService::addUniqueUser($data);
    }
}
