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
use Illuminate\Support\Facades\DB;
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
        TogglUser::where('user_id', $user->id)
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

    /**
     * Process the Time Entries for the User into the user_data table
     * 
     * @param User $user
     * @return StandardDTO
     */
    public function processTimeEntries(User $user): StandardDTO
    {
        // set up the Project IDs for this user
        $projects = TogglProject::where([
            ['user_id', '=', $user->id],
            ['attribute', '!=', null]
        ])->get();

        $projectIds = "";
        foreach ($projects as $project) {
            $projectIds .= "," . $project->project_id;
        }
        $projectIds = substr($projectIds, 1);
        
        $days = config('services.baseDays') * -1;
        $startAt = new Carbon();
        $startAt->addDays($days);

        $page = 1;

        $timeResponse = $this->api->getTimeEntries($user, $projectIds, $startAt->format('Y-m-d'), $page);
        if ($timeResponse === null) {
            $unauthorizedCount = ServiceLog::where('user_id', $user->id)
                ->where('service', 'toggl')
                ->where('unauthorized', true)
                ->whereNull('message')
                ->count();

            if ($unauthorizedCount > 0) {
                ServiceLog::where('user_id', $user->id)
                    ->where('service', 'toggl')
                    ->where('unauthorized', true)
                    ->whereNull('message')
                    ->update(['message' => 'Authorization revoked']);

                $this->disconnect($user, "Authorization revoked", true);
            }
            
            return new StandardDTO(
                success: false,
                message: __('app.togglTimeEntriesError')
            );
        }

        // to support the pagination, wrap in a do/while loop
        do {

            foreach ($timeResponse->data as $time) {
                $project = TogglProject::where('user_id', $user->id)
                    ->where('project_id', $time['pid'])
                    ->where('active_flag', true)
                    ->where('deleted_flag', false)
                    ->whereNotNull('attribute')
                    ->first();

                if ($project !== null) {
                    $endDT = new Carbon($time['end'], "UTC");
                    $endDT->setTimezone($user->existUser->timezone);

                    $service_id = $time['id'];
                    $date_id = $endDT->format('Y-m-d');
                    $attribute = $project->attribute;
                    $value = round($time['dur'] / 1000 / 60);
                    $this->processTimeEntry($user, $service_id, $attribute, $date_id, $value);
                }
            }
            
            if ($timeResponse->total_count > ($timeResponse->per_page * $page)) {
                $page++;
                $timeResponse = $this->api->getTimeEntries($user, $projectIds, $startAt->format('Y-m-d'), $page);
            } else {
                break;
            }

        } while (true);
        
        return new StandardDTO(
            success: true
        );
    }

    /**
     * Process the time entry by figuring out if it's already sent or has changed the totals
     * 
     * @param User $user
     * @param string $service_id
     * @param string $attribute
     * @param string $date_id
     * @param float $value
     * @return StandardDTO
     */
    private function processTimeEntry(User $user, string $service_id, string $attribute, string $date_id, float $value): StandardDTO
    {

        $dataCheck = DB::table('user_data')
            ->selectRaw('date_id, MAX(service_id2) AS id2, SUM(value) AS totalValue')
            ->where('user_id', $user->id)
            ->where('service', 'toggl')
            ->where('service_id', $service_id)
            ->groupBy('date_id')
            ->get();

        if ($dataCheck->count() == 0) {
            // Scenario 1 - new record
            $this->createUserData($user->id, $service_id, 1, $attribute, $date_id, $value);
        } else {
            // Scenario 2 - the Date Changed
            if (!$dataCheck->contains('date_id', '=', $date_id)) {
                foreach ($dataCheck as $record) {
                    $this->createUserData($user->id, $service_id, $record->id2 + 1, $attribute, $record->date_id, $record->totalValue * (-1));
                }
                $this->createUserData($user->id, $service_id, 1, $attribute, $date_id, $value);
            } else {
                $record = $dataCheck->where('date_id', '=', $date_id)->first();
                if ($record->totalValue != $value) {
                    $this->createUserData($user->id, $service_id, $record->id2 + 1, $attribute, $date_id, $value - $record->totalValue);
                }
            }
        }
        
        return new StandardDTO(
            success: true
        );
    }

    /**
     * Create a new record in the user_data table for YNAB
     * 
     * @param string $user_id
     * @param string $service_id
     * @param string $service_id2
     * @param string $attribute
     * @param string $date_id
     * @param int $value
     */
    private function createUserData(string $user_id, string $service_id, int $service_id2, string $attribute, string $date_id, int $value)
    {
        UserData::create([
            'user_id' => $user_id,
            'service' => 'toggl',
            'service_id' => $service_id,
            'service_id2' => $service_id2,
            'attribute' => $attribute,
            'date_id' => $date_id,
            'value' => $value
        ]);
    }

}