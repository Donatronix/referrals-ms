<?php


namespace App\Listeners;

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
        \App\Services\ReferralCodeService::checkUser($data);
    }
}
