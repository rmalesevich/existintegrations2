<?php

namespace App\Services;

use App\Models\ServiceLog;
use App\Models\User;
use App\Models\UserAttribute;
use App\Models\UserData;
use App\Models\WhatPulseUser;
use App\Objects\StandardDTO;
use App\Services\ApiIntegrations\WhatPulseApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WhatPulseService
{
    private $api;
    private $exist;

    public function __construct(WhatPulseApiService $api, ExistService $exist)
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
    public function connect(User $user, string $accountName): StandardDTO
    {
        if (WhatPulseUser::where('account_name', $accountName)->exists()) {
            return new StandardDTO(
                success: false,
                message: __('app.alreadyIntegrated', [ 'service' => 'WhatPulse'] )
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
            'account_name' => $userDetailsResponse->AccountName,
            'is_new' => true
        ]);

        ServiceLog::create([
            'user_id' => $user->id,
            'service' => 'whatpulse',
            'message' => 'Connected to WhatPulse'
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
        UserData::where('user_id', $user->id)
            ->where('service', 'whatpulse')
            ->delete();
        UserAttribute::where('user_id', $user->id)
            ->where('integration', 'whatpulse')
            ->delete();
        WhatPulseUser::where('id', $user->whatPulseUser->id)
            ->delete();
        
        Log::info(sprintf("WHATPULSE DISCONNECT: User ID %s via trigger %s", $user->id, $trigger));

        ServiceLog::create([
            'user_id' => $user->id,
            'service' => 'trakt',
            'message' => 'Disconnected from WhatPulse. Via trigger ' . $trigger
        ]);
        
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
        $days = config('services.baseDays') * -1;
        
        $currentDT = new Carbon("now", "UTC");
        $end = $currentDT->getTimestamp();
        $start = $currentDT->addDays($days)->getTimestamp();

        $pulseResponse = $this->api->getPulses($user->whatpulseUser->account_name, $start, $end);
        if ($pulseResponse === null) {
            return new StandardDTO(
                success: false,
                message: __('app.whatpulsePulseError')
            );
        }

        $userAttributes = UserAttribute::where('user_id', $user->id)
            ->where('integration', 'whatpulse')
            ->get();

        // store everything in the database
        foreach ($pulseResponse->data as $pulse) {
            $pulseDateDT = new Carbon($pulse->Timedate, "UTC");
            $pulseDateDT->setTimezone($user->existUser->timezone);
            $pulseDate = $pulseDateDT->format('Y-m-d H:i:s');

            $dateId = date('Y-m-d', strtotime($pulseDate));

            foreach ($userAttributes as $attribute) {
                switch ($attribute->attribute) {
                    case 'keystrokes':
                        $value = $pulse->Keys;
                        break;
                    case "mouse_clicks":
                        $value = $pulse->Clicks;
                        break;
                    case "download_mb":
                        $value = $pulse->DownloadMB;
                        break;
                    case "upload_mb":
                        $value = $pulse->UploadMB;
                        break;
                }

                UserData::updateOrCreate([
                    'user_id' => $user->id,
                    'service' => 'whatpulse',
                    'service_id' => $pulse->PulseID,
                    'attribute' => $attribute->attribute,
                    'date_id' => $dateId
                ], [
                    'value' => $value
                ]);
            }
        }

        return new StandardDTO(
            success: true
        );
    }

}