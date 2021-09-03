<?php

namespace App\Listeners;

use App\Services\RemoteService;


class AccrualRemunerationListener
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
        RemoteService::accrualRemuneration($data);
    }
}
