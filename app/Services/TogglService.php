<?php

namespace App\Services;

use App\Services\ApiIntegrations\TogglApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TogglService
{
    private $api;
    private $exist;

    public function __construct(TogglApiService $api, ExistService $exist)
    {
        $this->api = $api;
        $this->exist = $exist;
    }

}