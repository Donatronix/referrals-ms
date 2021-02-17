<?php

namespace App\Console;

use App\Services\MatomoAnalytics;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\KeyGenerateCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run get data from Matoma Analytics
        //$schedule->call(new MatomoAnalytics)->daily();
        $schedule->call(new MatomoAnalytics)->everyMinute();
    }

    /**
     * Get the timezone that should be used by default for scheduled events.
     *
     * @return string
     */
    /**
     * @return string
     */
    protected function scheduleTimezone(): string
    {
        return 'Europe/London';
    }
}
