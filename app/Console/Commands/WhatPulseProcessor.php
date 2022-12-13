<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserData;
use App\Services\ExistService;
use App\Services\WhatPulseService;
use Illuminate\Console\Command;
use Illuminate\Log\Logger;
use Illuminate\Support\Str;

class WhatPulseProcessor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatpulse:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the WhatPulse Users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Logger $logger, WhatPulseService $whatpulse, ExistService $exist)
    {
        $correlationId = (string) Str::uuid();
        $logger->info($correlationId . " beginning WhatPulseProcessor");

        $users = User::has('existUser')
            ->has('whatpulseUser')
            ->get();

        foreach ($users as $user) {
            $whatpulse->processPulses($user);

            // process any zero out requests to Exist
            $exist->sendUserData($user, "whatpulse", true);

            // send the data to Exist
            $exist->sendUserData($user, "whatpulse", false);

            // delete user data older than the base days
            $days = config('services.logDaysKept');
            $maxDate = date("Y-m-d", strtotime("-$days days"));

            UserData::where('user_id', $user->id)
                ->where('service', 'whatpulse')
                ->where('date_id', '<', $maxDate)
                ->delete();
        }

        $logger->info($correlationId . " finished WhatPulseProcessor");
    }
}
