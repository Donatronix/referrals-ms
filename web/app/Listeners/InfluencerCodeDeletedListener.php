<?php

namespace App\Listeners;

use App\Models\ReferralCode;

class InfluencerCodeDeletedListener
{
    /**
     * Handle the event.
     *
     * @param array $event
     *
     * @return void
     */
    public function handle(array $event): void
    {
        $receivedData = collect($event);

        $rc = ReferralCode::query()
            ->where('user_id', $receivedData->user_id)
            ->where('application_id', 'g-met')
            ->where('link', 'link' . $receivedData->code)
            ->delete();
    }
}
