<?php

namespace App\Services\ApiIntegrations;

use App\Objects\ApiRequestDTO;
use App\Services\AbstractApiService;

class WhatPulseApiService extends AbstractApiService
{
    public function getUserDetails(string $accountName)
    {
        $uri = 'https://api.whatpulse.org/user.php?format=json&user=' . $accountName;

        $apiRequest = new ApiRequestDTO(
            method: 'GET',
            uri: $uri
        );
        $userDetailResponse = $this->request($apiRequest);

        return $userDetailResponse;
    }
}