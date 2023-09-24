<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserData;
use App\Services\ExistService;
use App\Services\YnabService;
use Illuminate\Console\Command;
use Illuminate\Log\Logger;
use Illuminate\Support\Str;

class YnabProcessor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ynab:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the YNAB Users';

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
    public function handle(Logger $logger, YnabService $ynab, ExistService $exist)
    {
        $correlationId = (string) Str::uuid();
        $logger->info($correlationId . " beginning YnabProcessor");

        $users = User::has('existUser')
            ->has('ynabUser')
            ->get();

        foreach ($users as $user) {
            $existCheckResponse = $exist->checkExistUser($user);
            if (!$existCheckResponse->success) {
                continue;
            }
            
            $response = $ynab->processTransactions($user);
            if (!$response->success) {
                continue;
            }

            // process any zero out requests to Exist
            $exist->sendUserData($user, "ynab", true);

            // send the data to Exist
            $exist->sendUserData($user, "ynab", false);

            // delete user data older than the base days
            $days = config('services.logDaysKept');
            $maxDate = date("Y-m-d", strtotime("-$days days"));

            UserData::where('user_id', $user->id)
                ->where('service', 'ynab')
                ->where('date_id', '<', $maxDate)
                ->delete();
        }

        $logger->info($correlationId . " finished YnabProcessor");
    }
}
