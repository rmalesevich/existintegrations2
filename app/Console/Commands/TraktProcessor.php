<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserData;
use App\Services\ExistService;
use App\Services\TraktService;
use Illuminate\Console\Command;
use Illuminate\Log\Logger;
use Illuminate\Support\Str;

class TraktProcessor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trakt:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the Trakt Users';

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
    public function handle(Logger $logger, TraktService $trakt, ExistService $exist)
    {
        $correlationId = (string) Str::uuid();
        $logger->info($correlationId . " beginning TraktProcessor");

        $users = User::has('existUser')
            ->has('traktUser')
            ->get();

        foreach ($users as $user) {
            $existCheckResponse = $exist->checkExistUser($user);
            if (!$existCheckResponse->success) {
                continue;
            }
            
            $response = $trakt->processHistory($user);
            if (!$response->success) {
                continue;
            }

            // process any zero out requests to Exist
            $exist->sendUserData($user, "trakt", true);

            // send the data to Exist
            $exist->sendUserData($user, "trakt", false);

            // delete user data older than the base days
            $days = config('services.logDaysKept');
            $maxDate = date("Y-m-d", strtotime("-$days days"));

            UserData::where('user_id', $user->id)
                ->where('service', 'trakt')
                ->where('date_id', '<', $maxDate)
                ->delete();
        }

        $logger->info($correlationId . " finished TraktProcessor");
    }
}
