<?php

namespace App\Services;

use App\Services\ApiIntegrations\TraktApiService;
use Illuminate\Support\Facades\Log;

class TraktService
{
    public $api;

    public function __construct(TraktApiService $api)
    {
        $this->api = $api;
    }

}