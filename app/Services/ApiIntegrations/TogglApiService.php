<?php

namespace App\Services\ApiIntegrations;

use App\Objects\ApiRequestDTO;
use App\Objects\Toggl\TogglProjectDTO;
use App\Objects\Toggl\TogglUserDTO;
use App\Services\AbstractApiService;

class TogglApiService extends AbstractApiService
{
    /**
     * Get the User Details for the Toggl apiToken
     * 
     * @param string $apiToken
     * @return TogglUserDTO
     */
    public function getUserDetails(string $apiToken): ?TogglUserDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'GET',
            uri: config('services.toggl.baseUri') . '/me',
            params: [
                'auth' => [
                    $apiToken, 'api_token'
                ]
            ]
        );

        $userResponse = $this->request($apiRequest);
        if ($userResponse->success && $userResponse->responseBody !== null) {
            return new TogglUserDTO($userResponse->responseBody);
        } else {
            return null;
        }
    }

    /**
     * Get the Projects from Toggl for this User
     * 
     * @param string $apiToken
     * @param int $workspaceId
     * @return TogglProjectDTO
     */
    public function getProjects(string $apiToken, int $workspaceId): ?TogglProjectDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'GET',
            uri: config('services.toggl.baseUri') . '/workspaces/' . $workspaceId . '/projects',
            params: [
                'auth' => [
                    $apiToken, 'api_token'
                ]
            ]
        );

        $projectResponse = $this->request($apiRequest);
        if ($projectResponse->success && $projectResponse->responseBody !== null) {
            return TogglProjectDTO::fromRequest($projectResponse->responseBody);
        } else {
            return null;
        }
    }
}