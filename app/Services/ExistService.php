<?php

namespace App\Services;

use App\Services\ApiIntegrations\ExistApiService;

class ExistService
{
    public $api;

    public function __construct(ExistApiService $api)
    {
        $this->api = $api;
    }

}