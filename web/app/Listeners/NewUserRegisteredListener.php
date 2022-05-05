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

        //get country from phone number
        $country = $this->getCountry($user->phone_number);

        $username = $user->username;
        $id = $user->id;

        User::query()->create([
            'user_id' => $id,
            'username' => $username,
        ]);

    }
}
