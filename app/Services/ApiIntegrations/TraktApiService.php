<?php

namespace App\Services\ApiIntegrations;

use App\Models\User;
use App\Objects\ApiRequestDTO;
use App\Services\AbstractApiService;

class TraktApiService extends AbstractApiService
{
    public function __construct()
    {
        $this->clientId = env('TRAKT_CLIENT_ID');
        $this->clientSecret = env('TRAKT_CLIENT_SECRET');
    }
    
    /**
     * Generate the URI for the OAuth 2.0 authorization flow
     * 
     * @return string
     */
    public function getOAuthStarUri(): ?string
    {
        $uri = config('services.trakt.authUri') .
            "?response_type=code&client_id=" . $this->clientId . "&redirect_uri=" . route('trakt.connected');

        return $uri;
    }
 
}