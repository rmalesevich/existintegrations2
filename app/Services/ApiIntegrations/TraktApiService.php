<?php

namespace App\Services\ApiIntegrations;

use App\Models\User;
use App\Objects\ApiRequestDTO;
use App\Objects\Trakt\TraktAccountProfileDTO;
use App\Objects\Trakt\TraktEpisodeDTO;
use App\Objects\Trakt\TraktHistoryDTO;
use App\Objects\Trakt\TraktMovieDTO;
use App\Objects\Trakt\TraktOAuthTokenDTO;
use App\Services\AbstractApiService;
use Carbon\Carbon;

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
     * Refresh the OAuth Access Token using the Refresh Token
     * 
     * @param string $refreshToken
     * @return TraktOAuthTokenDTO
     */
    public function refreshToken(string $refreshToken): ?TraktOAuthTokenDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'POST',
            uri: config('services.trakt.tokenUri'),
            params: [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
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

    /**
     * Retrieve the watch history from the user.
     * Reference URL: https://trakt.docs.apiary.io/#reference/users/history/get-watched-history
     * 
     * @param User $user
     * @param DateTime $startAt
     * @param DateTime $endAt
     */
    public function getHistory(User $user, Carbon $startAt, Carbon $endAt): ?TraktHistoryDTO
    {
        $uri = config('services.trakt.baseUri') . '/users/me/history?start_at=' .
            $startAt->format('Y-m-d\TH:i:s.000\Z') . '&end_at=' . $endAt->format('Y-m-d\TH:i:s.000\Z') . '&limit=250';

        $apiRequest = new ApiRequestDTO(
            method: 'GET',
            uri: $uri,
            params: [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'trakt-api-version' => 2,
                    'trakt-api-key' => $this->clientId,
                    'Authorization' => 'Bearer ' . $user->traktUser->access_token
                ]
            ]
        );

        $historyResponse = $this->request($apiRequest);
        if ($historyResponse->success && $historyResponse->responseBody !== null) {
            return TraktHistoryDTO::fromRequest($historyResponse->responseBody);
        } else {
            return null;
        }
    }

    /**
     * Get the Movie details from the Trakt service
     * Reference URL: https://trakt.docs.apiary.io/#reference/movies/summary
     * 
     * @param string $id
     * @return TraktMovieDTO
     */
    public function getMovie(string $id): ?TraktMovieDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'GET',
            uri: config('services.trakt.baseUri') . '/movies/' . $id . '?extended=full',
            params: [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'trakt-api-version' => 2,
                    'trakt-api-key' => $this->clientId
                ]
            ]
        );

        $movieResponse = $this->request($apiRequest);
        if ($movieResponse->success && $movieResponse->responseBody !== null) {
            return new TraktMovieDTO($movieResponse->responseBody);
        } else {
            return null;
        }
    }

    /**
     * Get the Episode Details from Trakt. The ID is the ID for the show and not the episode.
     * Reference URL: https://trakt.docs.apiary.io/#reference/episodes/summary/get-a-single-episode-for-a-show
     * 
     * @param string $id
     * @param int $season
     * @param int $number
     * @return TraktEpisodeDTO
     */
    public function getEpisode(string $id, int $season, int $number): ?TraktEpisodeDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'GET',
            uri: config('services.trakt.baseUri') . '/shows/' . $id . '/seasons/'. $season . '/episodes/' . $number . '?extended=full',
            params: [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'trakt-api-version' => 2,
                    'trakt-api-key' => $this->clientId
                ]
            ]
        );

        $episodeResponse = $this->request($apiRequest);
        if ($episodeResponse->success && $episodeResponse->responseBody !== null) {
            return new TraktEpisodeDTO($episodeResponse->responseBody);
        } else {
            return null;
        }
    }
 
}