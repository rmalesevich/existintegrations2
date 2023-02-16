<?php

namespace App\Services\ApiIntegrations;

use App\Objects\ApiRequestDTO;
use App\Objects\WhatPulse\WhatPulsePulseDTO;
use App\Objects\WhatPulse\WhatPulseUserDTO;
use App\Services\AbstractApiService;

class WhatPulseApiService extends AbstractApiService
{
    /**
     * Retrieve the account details from the WhatPulse User API.
     * Documentation Reference: https://help.whatpulse.org/api/web-api#user-stats
     * 
     * @param string $accountName
     * @return WhatPulseUserDTO
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

    /**
     * Retrieve the pulses from the WhatPulse Pulses API within the Timestamp range
     * Documentation Reference: https://help.whatpulse.org/api/web-api/#pulse-stats
     * 
     * @param string $accountName
     * @param string $start
     * @param string $end
     * @return WhatPulsePulseDTO
     */
    public function getPulses(string $accountName, string $start, string $end): ?WhatPulsePulseDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'GET',
            uri: 'https://api.whatpulse.org/pulses.php?format=json&user=' . $accountName . '&start=' . $start . '&end=' . $end
        );

        $pulseResponse = $this->request($apiRequest);
        if ($pulseResponse->success && $pulseResponse->responseBody !== null) {
            if (Arr::exists($pulseResponse->responseBody, 'error')) return null;
            
            return WhatPulsePulseDTO::fromRequest($pulseResponse->responseBody);
        } else {
            return null;
        }
    }
}