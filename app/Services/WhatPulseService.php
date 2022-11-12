<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAttribute;
use App\Models\WhatPulsePulses;
use App\Models\WhatPulseUser;
use App\Objects\StandardDTO;
use App\Services\ApiIntegrations\ExistApiService;
use App\Services\ApiIntegrations\WhatPulseApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WhatPulseService
{
    public $api;

    public function __construct(WhatPulseApiService $api, ExistApiService $exist)
    {
        $this->api = $api;
        $this->exist = $exist;
    }

    /**
     * Connect the WhatPulse Account Name to this Exist User
     * 
     * @param User $user
     * @param string $accountName
     * @return StandardDTO
     */
    public function connect(User $user, $accountName): StandardDTO
    {
        if (WhatPulseUser::where('account_name', $accountName)->exists()) {
            return new StandardDTO(
                success: false,
                message: "WhatPulse account is already connected to Exist Integrations"
            );
        }

        $userDetailsResponse = $this->api->getUserDetails($accountName);

        if ($userDetailsResponse === null || $userDetailsResponse->error !== null) {
            return new StandardDTO(
                success: false,
                message: "No details were retrieved for this user from WhatPulse"
            );
        }

        WhatPulseUser::create([
            'user_id' => $user->id,
            'account_name' => $userDetailsResponse->AccountName
        ]);

        return new StandardDTO(
            success: true
        );
    }

    /**
     * Disconnect Exist Integrations from this user by removing any data associated with it
     * 
     * @param User $user
     * @param string $trigger
     * @return StandardDTO
     */
    public function disconnect(User $user, string $trigger = ""): StandardDTO
    {
        WhatPulsePulses::where('user_id', $user->id)
            ->delete();
        UserAttribute::where('user_id', $user->id)
            ->where('integration', 'whatpulse')
            ->delete();
        WhatPulseUser::where('id', $user->whatPulseUser->id)
            ->delete();
        
        Log::info(sprintf("WHATPULSE DISCONNECT: User ID %s via trigger %s", $user->id, $trigger));
        
        return new StandardDTO(
            success: true
        );
    }

    /**
     * Load the Pulses for this User and process them into the database
     * 
     * @param User $user
     * @return StandardDTO
     */
    public function processPulses(User $user): StandardDTO
    {
        $currentDT = new Carbon("now", "UTC");
        $end = $currentDT->getTimestamp();
        $start = $currentDT->addDays(-7)->getTimestamp();

        $pulseResponse = $this->api->getPulses($user->whatpulseUser->account_name, $start, $end);
        if ($pulseResponse === null) {
            return new StandardDTO(
                success: false,
                message: "Error loading the pulses for this user"
            );
        }

        // store everything in the database
        foreach ($pulseResponse->data as $pulse) {
            $pulseDateDT = new Carbon($pulse->Timedate, "UTC");
            $pulseDateDT->setTimezone($user->existUser->timezone);
            $pulseDate = $pulseDateDT->format('Y-m-d H:i:s');

            $dateId = date('Y-m-d', strtotime($pulseDate));

            WhatPulsePulses::updateOrCreate([
                'user_id' => $user->id,
                'pulse_id' => $pulse->PulseID
            ], [
                'date_id' => $dateId,
                'pulse_date' => $pulseDate,
                'keystrokes' => $pulse->Keys,
                'mouse_clicks' => $pulse->Clicks,
                'download_mb' => $pulse->DownloadMB,
                'upload_mb' => $pulse->UploadMB,
                'uptime_minutes' => $pulse->UptimeSeconds / 60
            ]);
        }

        return new StandardDTO(
            success: true
        );
    }

}