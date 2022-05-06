<?php

namespace App\Listeners;

use App\Events\NewUserRegistered;
use App\Models\User;
use App\Traits\GetCountryTrait;

class NewUserRegisteredListener
{
    use GetCountryTrait;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param NewUserRegistered $event
     *
     * @return void
     */
    public function handle(mixed $event)
    {
        $user = $event->user;
        $referrer_id = $event->referrer_id;

        //get country from phone number
        $country = $this->getCountry($user->phone_number);

        $id = $user->id;

        User::query()->create([
            'id' => $id,
            'country' => $country,
            'referrer_id' => $referrer_id,
        ]);

    }
}
