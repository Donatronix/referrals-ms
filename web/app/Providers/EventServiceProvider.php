<?php

namespace App\Providers;

use App\Listeners\InvitedReferralResponseListener;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'ReferralBonus' => [
            'App\Listeners\ReferralBonusListener',
        ],
        'JoinUserToReferralProgramRequest' => [
            'App\Listeners\JoinUserRequestListener',
        ],
        'SendReward' => [
            'App\Listeners\AccrualRemunerationListener',
        ],
        'InvitedReferralResponse' => [
            InvitedReferralResponseListener::class
        ]
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
