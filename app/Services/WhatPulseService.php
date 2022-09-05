<?php

namespace App\Services;

use App\Models\User;
use App\Models\WhatPulseUser;
use App\Objects\StandardDTO;
use App\Services\ApiIntegrations\WhatPulseApiService;
use Illuminate\Support\Facades\Log;

class WhatPulseService
{
    public $api;

    public function __construct(WhatPulseApiService $api)
    {
        $this->api = $api;
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
        WhatPulseUser::find($user->whatPulseUser->id)->delete();

        Log::info(sprintf("EXIST DISCONNECT: User ID %s via trigger %s", $user->id, $trigger));
        
        return new StandardDTO(
            success: true
        );
    }

}