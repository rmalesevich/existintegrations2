<?php

namespace App\Services\ApiIntegrations;

use App\Models\ServiceLog;
use App\Models\User;
use App\Objects\ApiRequestDTO;
use App\Objects\Ynab\YnabCategoryDTO;
use App\Objects\Ynab\YnabOAuthTokenDTO;
use App\Objects\Ynab\YnabTransactionDTO;
use App\Objects\Ynab\YnabUserDTO;
use App\Services\AbstractApiService;
use Carbon\Carbon;

class YnabApiService extends AbstractApiService
{
    private $clientId;
    private $clientSecret;
    
    public function __construct()
    {
        $this->clientId = env('YNAB_CLIENT_ID');
        $this->clientSecret = env('YNAB_CLIENT_SECRET');
    }
    
    /**
     * Generate the URI for the OAuth 2.0 authorization flow
     * 
     * @return string
     */
    public function getOAuthStarUri(): ?string
    {
        $uri = config('services.ynab.authUri') .
            "?response_type=code&client_id=" . $this->clientId . "&redirect_uri=" . route('ynab.connected') . "&scope=read-only";

        return $uri;
    }

    /**
     * Call the OAuth Token end point to exchange the code for a token
     * Reference URL: https://api.youneedabudget.com/#outh-applications
     * 
     * @param string $code
     * @return YnabOAuthTokenDTO
     */
    public function exchangeCodeForToken(string $code): ?YnabOAuthTokenDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'POST',
            uri: config('services.ynab.tokenUri'),
            params: [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri' => route('ynab.connected')
                ]
            ]
        );

        $tokenResponse = $this->request($apiRequest);
        if ($tokenResponse->success && $tokenResponse->responseBody !== null) {
            return new YnabOAuthTokenDTO($tokenResponse->responseBody);
        } else {
            return null;
        }
    }

    /**
     * Refresh the OAuth Access Token using the Refresh Token
     * 
     * @param string $refreshToken
     * @return YnabOAuthTokenDTO
     */
    public function refreshToken(string $refreshToken): ?YnabOAuthTokenDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'POST',
            uri: config('services.ynab.tokenUri'),
            params: [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri' => route('ynab.connected')
                ]
            ]
        );

        $tokenResponse = $this->request($apiRequest);
        if ($tokenResponse->success && $tokenResponse->responseBody !== null) {
            return new YnabOAuthTokenDTO($tokenResponse->responseBody);
        } else {
            return null;
        }
    }
 
    /**
     * Retrieve the User from the YNAB API.
     * Reference URL: https://api.youneedabudget.com/v1#/User
     * 
     * @param User $user
     * @return YnabUserDTO
     */
    public function getUser(User $user): ?YnabUserDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'GET',
            uri: config('services.ynab.baseUri') . '/user',
            params: [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $user->ynabUser->access_token
                ]
            ]
        );

        $userResponse = $this->request($apiRequest);
        if ($userResponse->success && $userResponse->responseBody !== null) {
            return new YnabUserDTO($userResponse->responseBody['data']['user']);
        } else {
            return null;
        }
    }

    /**
     * Retrieve the Categories for the user for the last-used budget from the YNAB API.
     * Reference URL: https://api.youneedabudget.com/v1#/Categories/getCategories
     * 
     * @param User $user
     * @return YnabCategoryDTO
     */
    public function getCategories(User $user): ?YnabCategoryDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'GET',
            uri: config('services.ynab.baseUri') . '/budgets/last-used/categories',
            params: [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $user->ynabUser->access_token
                ]
            ]
        );

        $userResponse = $this->request($apiRequest);
        if ($userResponse->success && $userResponse->responseBody !== null) {
            return new YnabCategoryDTO($userResponse->responseBody);
        } else {
            return null;
        }
    }

    /**
     * Retrieve the Transactions from the endpoint for the User
     * Reference URL: https://api.youneedabudget.com/v1#/Transactions/getTransactions
     * 
     * Supports the 401 unauthorized / ServerLog function
     * 
     * @param User $user
     * @param string $sinceDate
     * @return YnabTransactionDTO
     */
    public function getTransactions(User $user, string $sinceDate): ?YnabTransactionDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'GET',
            uri: config('services.ynab.baseUri') . '/budgets/last-used/transactions?since_date=' . $sinceDate,
            params: [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $user->ynabUser->access_token
                ]
            ]
        );

        $transactionResponse = $this->request($apiRequest);
        if ($transactionResponse->success && $transactionResponse->responseBody !== null) {
            return new YnabTransactionDTO($transactionResponse->responseBody);
        } else if (!$transactionResponse->success && $transactionResponse->statusCode == 401) {
            ServiceLog::create([
                'user_id' => $user->id,
                'service' => 'ynab',
                'unauthorized' => true
            ]);

            return null;
        } else {
            return null;
        }
    }
 
}