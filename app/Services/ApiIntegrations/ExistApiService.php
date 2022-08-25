<?php

namespace App\Services\ApiIntegrations;

use App\Objects\ApiRequestDTO;
use App\Services\AbstractApiService;

class ExistApiService extends AbstractApiService
{
    public function __construct()
    {
        $this->clientId = env('EXIST_CLIENT_ID');
    }
    
    /**
     * Generate the URI for the OAuth 2.0 authorization flow
     * 
     * @return string
     */
    public function getOAuthStarUri(): ?string
    {
        $uri = config('services.exist.authUri') .
            "?response_type=code&client_id=" . $this->clientId . "&scope=" . config('services.exist.scope');

        return $uri;
    }
    
    
}