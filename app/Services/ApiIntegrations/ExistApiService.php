<?php

namespace App\Services\ApiIntegrations;

use App\Models\User;
use App\Objects\ApiRequestDTO;
use App\Objects\Exist\ExistAccountProfileDTO;
use App\Objects\Exist\ExistOAuthTokenDTO;
use App\Services\AbstractApiService;

class ExistApiService extends AbstractApiService
{
    public function __construct()
    {
        $this->clientId = env('EXIST_CLIENT_ID');
        $this->clientSecret = env('EXIST_CLIENT_SECRET');
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
    
    /**
     * Call the OAuth Token end point to exchange the code for a token
     * 
     * @param string $code
     * @return ExistOAuthTokenDTO
     */
    public function exchangeCodeForToken(string $code): ?ExistOAuthTokenDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'POST',
            uri: config('services.exist.tokenUri'),
            params: [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret
                ]
            ]
        );

        $tokenResponse = $this->request($apiRequest);
        if ($tokenResponse->success || $tokenResponse->responseBody !== null) {
            return new ExistOAuthTokenDTO($tokenResponse->responseBody);
        } else {
            return null;
        }
    }

    /**
     * Refresh the OAuth Access Token using the Refresh Token
     * 
     * @param string $refreshToken
     * @return ExistOAuthTokenDTO
     */
    public function refreshToken(string $refreshToken): ?ExistOAuthTokenDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'POST',
            uri: config('services.exist.tokenUri'),
            params: [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret
                ]
            ]
        );

        $tokenResponse = $this->request($apiRequest);
        if ($tokenResponse->success || $tokenResponse->responseBody !== null) {
            return new ExistOAuthTokenDTO($tokenResponse->responseBody);
        } else {
            return null;
        }
    }

    /**
     * Retrieve the Account Profile from the Exist API.
     * Documentation Reference: https://developer.exist.io/reference/users/
     * 
     * @param User $user
     * @return ExistAccountProfileDTO
     */
    public function getAccountProfile(User $user): ?ExistAccountProfileDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'GET',
            uri: config('services.exist.baseUri') . '/accounts/profile/',
            params: [
                'headers' => [
                    'Authorization' => 'Bearer ' . $user->existUser->access_token
                ]
            ]
        );

        $accountProfileResponse = $this->request($apiRequest);
        if ($accountProfileResponse->success || $accountProfileResponse->responseBody !== null) {
            return new ExistAccountProfileDTO($accountProfileResponse->responseBody);
        } else {
            return null;
        }
    }
}