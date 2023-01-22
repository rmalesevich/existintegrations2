<?php

namespace App\Console;

use App\Console\Commands\TogglProcessor;
use App\Console\Commands\TraktProcessor;
use App\Console\Commands\WhatPulseProcessor;
use App\Console\Commands\YnabProcessor;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(WhatPulseProcessor::class)->hourlyAt(env('WHATPULSE_HOUR'));
        $schedule->command(TraktProcessor::class)->hourlyAt(env('TRAKT_HOUR'));
        $schedule->command(YnabProcessor::class)->hourlyAt(env('YNAB_HOUR'));
        $schedule->command(TogglProcessor::class)->hourlyAt(env('TOGGL_HOUR'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
