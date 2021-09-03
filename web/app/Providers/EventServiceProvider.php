<?php

namespace App\Providers;

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
        'ReferralUserInfo' => [
            'App\Listeners\UsersMeetListener',
        ],
        'SendReward' => [
            'App\Listeners\AccrualRemunerationListener',
        ],
    ];
}
