<?php

namespace App\Listeners;

use App\Services\ReferralCodeService;

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
        ReferralCodeService::addUniqueUser($data);
    }
}
