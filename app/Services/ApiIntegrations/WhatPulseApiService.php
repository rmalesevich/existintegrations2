<?php

namespace App\Services\ApiIntegrations;

use App\Services\AbstractApiService;

class WhatPulseApiService extends AbstractApiService
{
    public function getUserDetails(string $accountName)
    {
        $uri = 'https://api.whatpulse.org/user.php?format=json&user=' . $accountName;
        $userDetailResponse = $this->request('GET', $uri);

        return $userDetailResponse;
    }
}