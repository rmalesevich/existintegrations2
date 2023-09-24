<?php

namespace App\Services\ApiIntegrations;

use App\Models\User;
use App\Models\ServiceLog;
use App\Objects\ApiRequestDTO;
use App\Objects\Exist\ExistAccountProfileDTO;
use App\Objects\Exist\ExistAttributeDTO;
use App\Objects\Exist\ExistOAuthTokenDTO;
use App\Objects\Exist\ExistStatusDTO;
use App\Objects\Exist\ExistAttributesOwnedDTO;
use App\Services\AbstractApiService;

class ExistApiService extends AbstractApiService
{
    private $clientId;
    private $clientSecret;
    
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
        if ($tokenResponse->success && $tokenResponse->responseBody !== null) {
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
        if ($tokenResponse->success && $tokenResponse->responseBody !== null) {
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
        if ($accountProfileResponse->success && $accountProfileResponse->responseBody !== null) {
            return new ExistAccountProfileDTO($accountProfileResponse->responseBody);
        } else {
            return null;
        }
    }

    /**
     * Retrieve the Owned Attributes by the user and the service
     * Documentation Reference: https://developer.exist.io/reference/attribute_ownership/#list-owned-attributes
     * 
     * @param User $user
     * @return ExistAttributesOwnedDTO
     */
    public function getOwnedAttributes(User $user): ?ExistAttributesOwnedDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'GET',
            uri: config('services.exist.baseUri') . '/attributes/owned/',
            params: [
                'headers' => [
                    'Authorization' => 'Bearer ' . $user->existUser->access_token
                ]
            ]
        );

        $ownedAttributesResponse = $this->request($apiRequest);
        if ($ownedAttributesResponse->success && $ownedAttributesResponse->responseBody !== null) {
            return new ExistAttributesOwnedDTO($ownedAttributesResponse->responseBody);
        } else if (!$ownedAttributesResponse->success && $ownedAttributesResponse->statusCode == 401) {
            ServiceLog::create([
                'user_id' => $user->id,
                'service' => 'exist',
                'unauthorized' => true
            ]);

            return null;
        } else {
            return null;
        }
    }

    /**
     * Acquire the standard attributes held within the attribute body with Exist
     * Documentation Reference: https://developer.exist.io/reference/attribute_ownership/#acquire-attributes
     * 
     * @param User $user
     * @param array $attributeBody
     * @return ExistAttributeDTO
     */
    public function acquireAttribute(User $user, array $attributeBody): ?ExistAttributeDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'POST',
            uri: config('services.exist.baseUri') . '/attributes/acquire/',
            params: [
                'headers' => [
                    'Authorization' => 'Bearer ' . $user->existUser->access_token
                ],
                'json' => $attributeBody
            ]
        );

        $acquireAttributeResponse = $this->request($apiRequest);
        if ($acquireAttributeResponse->success && $acquireAttributeResponse->responseBody !== null) {
            return new ExistAttributeDTO($acquireAttributeResponse->responseBody);
        } else {
            return null;
        }
    }

    /**
     * Release the standard attributes held within the attribute body with Exist
     * Documentation Reference: https://developer.exist.io/reference/attribute_ownership/#request
     * 
     * @param User $user
     * @param array $attributeBody
     * @return ExistAttributeDTO
     */
    public function releaseAttribute(User $user, array $attributeBody): ?ExistAttributeDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'POST',
            uri: config('services.exist.baseUri') . '/attributes/release/',
            params: [
                'headers' => [
                    'Authorization' => 'Bearer ' . $user->existUser->access_token
                ],
                'json' => $attributeBody
            ]
        );

        $releaseAttributeResponse = $this->request($apiRequest);
        if ($releaseAttributeResponse->success && $releaseAttributeResponse->responseBody !== null) {
            return new ExistAttributeDTO($releaseAttributeResponse->responseBody);
        } else {
            return null;
        }
    }

    /**
     * Create a new Attribute in Exist
     * Documentation Reference: https://developer.exist.io/reference/creating_attributes/#parameters
     * 
     * @param User $user
     * @param array $attributeBody
     * @return ExistAttributeDTO
     */
    public function createAttribute(User $user, array $attributeBody): ?ExistAttributeDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'POST',
            uri: config('services.exist.baseUri') . '/attributes/create/',
            params: [
                'headers' => [
                    'Authorization' => 'Bearer ' . $user->existUser->access_token
                ],
                'json' => $attributeBody
            ]
        );

        $createAttributeResponse = $this->request($apiRequest);
        if ($createAttributeResponse->success && $createAttributeResponse->responseBody !== null) {
            return new ExistAttributeDTO($createAttributeResponse->responseBody);
        } else {
            return null;
        }
    }

    /**
     * Use the Update endpoint to update the values to Exist
     * Documentation Reference: https://developer.exist.io/reference/writing_data/#update-attribute-values
     * 
     * @param User $user
     * @param array $payload
     * @return ExistStatusDTO
     */
    public function updateAttributeValue(User $user, array $payload): ?ExistStatusDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'POST',
            uri: config('services.exist.baseUri') . '/attributes/update/',
            params: [
                'headers' => [
                    'Authorization' => 'Bearer ' . $user->existUser->access_token
                ],
                'json' => $payload
            ]
        );

        $updateAttributeResponse = $this->request($apiRequest);
        if ($updateAttributeResponse->success && $updateAttributeResponse->responseBody !== null) {
            return new ExistStatusDTO($updateAttributeResponse->responseBody);
        } else {
            return null;
        }
    }

    /**
     * Use the Increment endpoint to update the values to Exist
     * Documentation Reference: https://developer.exist.io/reference/writing_data/#increment-attribute-values
     * 
     * @param User $user
     * @param array $payload
     * @return ExistStatusDTO
     */
    public function incrementAttributeValue(User $user, array $payload): ?ExistStatusDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'POST',
            uri: config('services.exist.baseUri') . '/attributes/increment/',
            params: [
                'headers' => [
                    'Authorization' => 'Bearer ' . $user->existUser->access_token
                ],
                'json' => $payload
            ]
        );

        $incrementAttributeResponse = $this->request($apiRequest);
        if ($incrementAttributeResponse->success && $incrementAttributeResponse->responseBody !== null) {
            return new ExistStatusDTO($incrementAttributeResponse->responseBody);
        } else {
            return null;
        }
    }
}