<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAttribute;
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
        WhatPulseUser::where('id', $user->whatPulseUser->id)->delete();
        UserAttribute::where('user_id', $user->id)
            ->where('integration', 'whatpulse')
            ->delete();

        Log::info(sprintf("EXIST DISCONNECT: User ID %s via trigger %s", $user->id, $trigger));
        
        return new StandardDTO(
            success: true
        );
    }

    /**
     * Connect these attributes to Exist and store in the database
     * 
     * @param User $user
     * @param array $attributes
     * @return StandardDTO
     */
    public function setAttributes(User $user, array $attributes): StandardDTO
    {
        // Delete any records that aren't in the passed in attributes
        UserAttribute::where('integration', 'whatpulse')
            ->whereNotIn('attribute', $attributes)
            ->delete();

        foreach ($attributes as $attribute) {
            // Add the records to the UserAttribute table
            UserAttribute::updateOrCreate([
                'user_id' => $user->id,
                'integration' => 'whatpulse',
                'attribute' => $attribute
            ]);
        }
        
        return new StandardDTO(
            success: true
        );
    }

}