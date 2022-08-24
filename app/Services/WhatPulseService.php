<?php

namespace App\Services;

use App\Services\ApiIntegrations\WhatPulseApiService;

class WhatPulseService
{
    public $api;

    public function __construct(WhatPulseApiService $api)
    {
        $this->api = $api;
    }

    public function connect($accountName)
    {
        return $this->api->getUserDetails($accountName);
    }
}