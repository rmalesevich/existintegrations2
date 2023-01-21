<?php

namespace App\Services;

use App\Models\ServiceLog;
use App\Models\TogglProject;
use App\Models\TogglUser;
use App\Models\User;
use App\Models\UserAttribute;
use App\Models\UserData;
use App\Objects\StandardDTO;
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

    /**
     * Connect the Toggl Track API Token to this Exist User
     * 
     * @param User $user
     * @param string $apiToken
     * @return StandardDTO
     */
    public function connect(User $user, string $apiToken): StandardDTO
    {
        if (TogglUser::where('api_token', $apiToken)->exists()) {
            return new StandardDTO(
                success: false,
                message: __('app.alreadyIntegrated', [ 'service' => 'Toggl Track'] )
            );
        }

        $userDetailsResponse = $this->api->getUserDetails($apiToken);
        if ($userDetailsResponse === null || $userDetailsResponse->data['default_wid'] === null) {
            return new StandardDTO(
                success: false,
                message: "No details were retrieved for the user associated with this API Token"
            );
        }

        $userId = $userDetailsResponse->data['id'];
        $workspaceId = $userDetailsResponse->data['default_wid'];
        $projectResponse = $this->api->getProjects($apiToken, $workspaceId);

        if ($projectResponse === null) {
            return new StandardDTO(
                success: false,
                message: "No projects were retrieved for the user associated with this API Token"
            );
        }

        TogglUser::create([
            'user_id' => $user->id,
            'api_token' => $apiToken,
            'external_user_id' => $userId,
            'external_workspace_id' => $workspaceId,
            'is_new' => true
        ]);

        foreach ($projectResponse->data as $project) {
            TogglProject::create([
                'user_id' => $user->id,
                'project_id' => $project->id,
                'project_name' => $project->name,
                'active_flag' => $project->active,
                'deleted_flag' => false,
                'attribute' => null
            ]);
        }

        ServiceLog::create([
            'user_id' => $user->id,
            'service' => 'toggl',
            'message' => 'Connected to Toggl Track'
        ]);
        
        return new StandardDTO(
            success: true
        );
    }

    /**
     * Disconnect Exist Integrations from this user by removing any data associated with it
     * 
     * @param User $user
     * @param string $trigger
     * @return StandardDTO
     */
    public function disconnect(User $user, string $trigger = ""): StandardDTO
    {
        UserData::where('user_id', $user->id)
            ->where('service', 'toggl')
            ->delete();
        UserAttribute::where('user_id', $user->id)
            ->where('integration', 'toggl')
            ->delete();
        TogglProject::where('user_id', $user->id)
            ->delete();
        TogglUser::where('id', $user->togglUser->id)
            ->delete();
        
        Log::info(sprintf("TOGGL DISCONNECT: User ID %s via trigger %s", $user->id, $trigger));

        ServiceLog::create([
            'user_id' => $user->id,
            'service' => 'trakt',
            'message' => 'Disconnected from Toggl Track. Via trigger ' . $trigger
        ]);
        
        return new StandardDTO(
            success: true
        );
    }

    /**
     * Process the Projects from Toggl Track into the database
     * 
     * @param User $user
     * @return StandardDTO
     */
    public function processProjects(User $user): StandardDTO
    {
        // get the projects from Toggl Track
        $projectResponse = $this->api->getProjects($user->togglUser->api_token, $user->togglUser->external_user_id);
        if ($projectResponse === null) {
            return new StandardDTO(
                success: false,
                message: __('app.categoryAPIFail', ['category' => 'Projects', 'service' => 'Toggl Track'])
            );
        }

        foreach ($projectResponse->data as $project) {
            TogglProject::updateOrCreate([
                'user_id' => $user->id,
                'project_id' => $project->id
            ], [
                'project_name' => $project->name,
                'active_flag' => $project->active
            ]);
        }

        return new StandardDTO(
            success: true
        );
    }

    /**
     * Update the Project in the database based on the user's management of the Toggl Track configuration
     * 
     * @param User $user
     * @param string $projectId
     * @param string $attribute
     * @return StandardDTO
     */
    public function updateProject(User $user, string $projectId, string $attribute): StandardDTO
    {
        if ($attribute == __('app.dropdownIgnore') ) {
            $attribute = null;
        }
        
        TogglProject::where('user_id', $user->id)
            ->where('project_id', $projectId)
            ->update([
                'attribute' => $attribute
            ]);

        return new StandardDTO(
            success: true
        );
    }

}