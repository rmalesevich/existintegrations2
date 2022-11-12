<?php

namespace App\Console\Commands;

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
    public function handle(Logger $logger, ExistService $exist, WhatPulseService $whatpulse)
    {
        echo "Hello!";
    }
}
