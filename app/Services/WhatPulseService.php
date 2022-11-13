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
use Illuminate\Support\Arr;
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
                'upload_mb' => $pulse->UploadMB
            ]);
        }

        return new StandardDTO(
            success: true
        );
    }

    /**
     * Loop through the WhatPulsePulses for the user and send the data to Exist.
     * If $zero = true, it will zero out the data already sent to Exist.
     * 
     * @param User $user
     * @param bool $zero
     * @return StandardDTO
     */
    public function sendToExist(User $user, bool $zero = false): StandardDTO
    {
        $pulses = WhatPulsePulses::where('user_id', $user->id)
            ->where('sent_to_exist', $zero)
            ->get();

        $attributes = UserAttribute::where('user_id', $user->id)
            ->where('integration', 'whatpulse')
            ->get();

        // build the total Payload
        $totalPayload = array();
        foreach ($pulses as $pulse) {
            foreach ($attributes as $attribute) {

                $name = $attribute->attribute;
                array_push($totalPayload, [
                    'name' => $name,
                    'date' => $pulse->date_id,
                    'value' => $zero ? 0 : $pulse->$name
                ]);
            }
        }

        $maxUpdate = config('services.exist.maxUpdate');
        $loops = ceil(sizeof($totalPayload) / $maxUpdate);

        for ($i = 0; $i < $loops; $i++) {
            $payload = array_slice($totalPayload, $i * $maxUpdate, $maxUpdate);

            if ($zero) {
                $status = $this->exist->updateAttributeValue($user, $payload);
            } else {
                $status = $this->exist->incrementAttributeValue($user, $payload);

                if ($status !== null) {
                    foreach ($status->success as $record) {
                        WhatPulsePulses::where('user_id', $user->id)
                            ->where('date_id', $record['date'])
                            ->where($record['name'], $record['value'])
                            ->update(['sent_to_exist' => true]);
                    }
                }
            }
            
        }
        
        return new StandardDTO(
            success: true
        );
    }

}