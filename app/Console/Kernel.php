<?php

namespace App\Console;

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
        $schedule->command(WhatPulseProcessor::class)->hourlyAt(15);
        $schedule->command(TraktProcessor::class)->hourlyAt(30);
        $schedule->command(YnabProcessor::class)->hourlyAt(45);
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
