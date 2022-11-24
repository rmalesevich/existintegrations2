<?php

namespace App\Services\ApiIntegrations;

use App\Models\User;
use App\Objects\ApiRequestDTO;
use App\Objects\Trakt\TraktAccountProfileDTO;
use App\Objects\Trakt\TraktOAuthTokenDTO;
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

    /**
     * Call the OAuth Token end point to exchange the code for a token
     * Reference URL: https://trakt.docs.apiary.io/#reference/authentication-oauth/get-token/exchange-code-for-access_token
     * 
     * @param string $code
     * @return TraktOAuthTokenDTO
     */
    public function exchangeCodeForToken(string $code): ?TraktOAuthTokenDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'POST',
            uri: config('services.trakt.tokenUri'),
            params: [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri' => route('trakt.connected')
                ]
            ]
        );

        $tokenResponse = $this->request($apiRequest);
        if ($tokenResponse->success && $tokenResponse->responseBody !== null) {
            return new TraktOAuthTokenDTO($tokenResponse->responseBody);
        } else {
            return null;
        }
    }

    /**
     * Retrieve the Account Profile from the Trakt API.
     * Reference URL: https://trakt.docs.apiary.io/#reference/users/profile/get-user-profile
     * 
     * @param User $user
     * @return TraktAccountProfileDTO
     */
    public function getAccountProfile(User $user): ?TraktAccountProfileDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'GET',
            uri: config('services.trakt.baseUri') . '/users/me',
            params: [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'trakt-api-version' => 2,
                    'trakt-api-key' => $this->clientId,
                    'Authorization' => 'Bearer ' . $user->traktUser->access_token
                ]
            ]
        );

        $accountProfileResponse = $this->request($apiRequest);
        if ($accountProfileResponse->success && $accountProfileResponse->responseBody !== null) {
            return new TraktAccountProfileDTO($accountProfileResponse->responseBody);
        } else {
            return null;
        }
    }
 
}