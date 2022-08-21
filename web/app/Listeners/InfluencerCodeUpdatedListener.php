<?php

namespace App\Listeners;

use App\Models\ReferralCode;

class InfluencerCodeUpdatedListener
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

        $rc = ReferralCode::query()->updateOrCreate([
            'user_id' => $receivedData->user_id,
            'application_id' => 'g-met',
            'link' => 'link' . $receivedData->oldCode,
        ], [
            'user_id' => $receivedData->user_id,
            'application_id' => 'g-met',
            'link' => 'link' . $receivedData->code,
            'is_default' => true,
            'note' => 'Influencer code',
        ]);

    }
}
