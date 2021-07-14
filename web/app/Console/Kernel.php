<?php

namespace App\Console;

use App\Console\Commands\KeyGenerateCommand;
use App\Console\Commands\RouteListCommand;
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
        KeyGenerateCommand::class,
        RouteListCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run get data from Matomo Analytics
        //$schedule->call(new MatomoAnalytics)->daily();

//        $schedule->call(new MatomoAnalytics, ['method' => 'UserId.getUsers'])->everyMinute();
//        $schedule->call(new MatomoAnalytics, ['method' => 'Live.getMostRecentVisitorId'])->everyMinute();
        $schedule->call(new MatomoAnalytics, ['method' => 'Actions.getPageUrls'])->everyMinute();


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
