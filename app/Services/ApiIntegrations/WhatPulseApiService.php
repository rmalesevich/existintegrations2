<?php

namespace App\Services\ApiIntegrations;

use App\Objects\ApiRequestDTO;
use App\Objects\WhatPulse\WhatPulseUserDTO;
use App\Services\AbstractApiService;

class WhatPulseApiService extends AbstractApiService
{
    /**
     * Retrieve the account details from the WhatPulse User API.
     * Documentation Reference: https://help.whatpulse.org/api/web-api#user-stats
     */
    public function getUserDetails(string $accountName): ?WhatPulseUserDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'GET',
            uri: 'https://api.whatpulse.org/user.php?format=json&user=' . $accountName
        );

        $userDetailResponse = $this->request($apiRequest);
        if ($userDetailResponse->success && $userDetailResponse->responseBody !== null) {
            return new WhatPulseUserDTO($userDetailResponse->responseBody);
        } else {
            return null;
        }
    }
}