<?php

namespace App\Listeners;

use App\Models\ReferralCode;

class InfluencerCodeCreatedListener
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

        $checkUser = ReferralCode::where('application_id', 'g-met',)
            ->where('link', 'link' . $receivedData->code)
            ->first();

        if ($checkUser->isEmpty()) {

            $rc = ReferralCode::query()->create([
                'user_id' => $receivedData->user_id,
                'application_id' => 'g-met',
                'link' => 'link' . $receivedData->code,
                'is_default' => true,
                'note' => 'Influencer code',
            ]);
        }
    }
}
