<?php

namespace App\Services\ApiIntegrations;

use App\Models\ServiceLog;
use App\Models\User;
use App\Objects\ApiRequestDTO;
use App\Objects\Toggl\TogglProjectDTO;
use App\Objects\Toggl\TogglTimeDTO;
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

    /**
     * Get the Time Entries for the passed in User's comma-separated list of project ids
     * 
     * @param User $user
     * @param string $projectIds
     * @param string $sinceDate
     * @param int $page
     * @return TogglTimeDTO
     */
    public function getTimeEntries(User $user, string $projectIds, string $sinceDate, int $page = 1): ?TogglTimeDTO
    {
        $apiRequest = new ApiRequestDTO(
            method: 'GET',
            uri: config('services.toggl.reportsBaseUri') . 
                '/details?workspace_id=' . $user->togglUser->external_workspace_id .
                '&project_ids=' . $projectIds .
                '&user_agent=' . config('services.toggl.userAgent') .
                '&page=' . $page .
                '&since=' . $sinceDate,
            params: [
                'auth' => [
                    $user->togglUser->api_token, 'api_token'
                ]
            ]
        );

        $timeResponse = $this->request($apiRequest);
        if ($timeResponse->success && $timeResponse->responseBody !== null) {
            return new TogglTimeDTO($timeResponse->responseBody);
        } else if (!$timeResponse->success && $timeResponse->statusCode == 401) {
            ServiceLog::create([
                'user_id' => $user->id,
                'service' => 'toggl',
                'unauthorized' => true
            ]);

            return null;
        } else {
            return null;
        }
    }
}