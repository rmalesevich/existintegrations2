<?php

namespace App\Services;

use App\Models\ExistUser;
use App\Models\User;
use App\Models\UserAttribute;
use App\Models\UserData;
use App\Objects\Exist\ExistStatusDTO;
use App\Objects\StandardDTO;
use App\Services\ApiIntegrations\ExistApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExistService
{
    public $api;

    public function __construct(ExistApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Complete the OAuth 2.0 authentication workflow with Exist
     * 
     * @param User $user
     * @param string $code
     * @return StandardDTO
     */
    public function authorize(User $user, string $code): StandardDTO
    {
        $oauthTokenResponse = $this->api->exchangeCodeForToken($code);
        if ($oauthTokenResponse === null) {
            return new StandardDTO(
                success: false,
                message: __('app.oAuthCodeError')
            );
        }

        ExistUser::create([
            'user_id' => $user->id,
            'access_token' => $oauthTokenResponse->access_token,
            'refresh_token' => $oauthTokenResponse->refresh_token,
            'token_expires' => date('Y-m-d H:i:s', (time() + $oauthTokenResponse->expires_in))
        ]);
        $user = User::find($user->id);

        $accountProfileResponse = $this->api->getAccountProfile($user);
        if ($accountProfileResponse === null) {
            ExistUser::find($user->existUser->id)->delete();
            return new StandardDTO(
                success: false,
                message: __('app.accountProfileAPIFail', ['service' => 'Exist'])
            );
        }

        ExistUser::find($user->existUser->id)
            ->update([
                'username' => $accountProfileResponse->username,
                'timezone' => $accountProfileResponse->timezone
            ]);
        
        return new StandardDTO(
            success: true,
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
        $whatpulse = app(WhatPulseService::class);
        $whatpulse->disconnect($user, "Exist disconnect");

        $trakt = app(TraktService::class);
        $trakt->disconnect($user, "Exist disconnect");

        $ynab = app(YnabService::class);
        $ynab->disconnect($user, "Exist disconnect");

        $toggl = app(TogglService::class);
        $toggl->disconect($user, "Exist disconnect");
        
        ExistUser::where('id', $user->existUser->id)->delete();
        UserAttribute::where('user_id', $user->id)
            ->delete();

        Log::info(sprintf("EXIST DISCONNECT: User ID %s via trigger %s", $user->id, $trigger));
        
        return new StandardDTO(
            success: true
        );
    }

    /**
     * Token the Token for the user to ensure it's still valid. If required, refresh the token from Exist.
     * Exist tokens last for 365 days, but just in case there is a weird case of timing this will refresh
     * the token 7 days before it expires.
     * 
     * @param User $user
     * @return StandardDTO
     */
    public function checkToken(User $user): StandardDTO
    {
        $todaysDate = date('Y-m-d H:i:s');
        $checkDate = date('Y-m-d H:i:s', strtotime("-7 days", strtotime($user->existUser->token_expires)));

        if ($todaysDate >= $checkDate) {
            $refreshTokenResponse = $this->api->refreshToken($user->existUser->refresh_token);
            if ($refreshTokenResponse === null) {
                return new StandardDTO(
                    success: false,
                    message: __('app.oAuthRefreshError', ['service' => 'Exist'])
                );
            }

            ExistUser::find($user->existUser->id)
                ->update([
                    'access_token' => $refreshTokenResponse->access_token,
                    'refresh_token' => $refreshTokenResponse->refresh_token,
                    'token_expires' => date('Y-m-d H:i:s', (time() + $refreshTokenResponse->expires_in))
                ]);
        }

        return new StandardDTO(
            success: true
        );
    }

    /**
     * Get the Account Profile from Exist and persist it in the database for the passed in user.
     * 
     * @param User $user
     * @return StandardDTO
     */
    public function updateAccountProfile(User $user): StandardDTO
    {
        $checkTokenResponse = $this->checkToken($user);
        if (!$checkTokenResponse->success) {
            return $checkTokenResponse;
        }

        $accountProfileResponse = $this->api->getAccountProfile($user);
        if ($accountProfileResponse === null) {
            return new StandardDTO(
                success: false,
                message: "Failed to retrieve profile information for your account from Exist."
            );
        }

        ExistUser::find($user->existUser->id)
            ->update([
                'username' => $accountProfileResponse->username,
                'timezone' => $accountProfileResponse->timezone
            ]);

        return new StandardDTO(
            success: true
        );
    }

    /**
     * Parse through an array of attributes from the UI and do the appropriate calls to Exist
     * to either set ownership, release ownership, or create the attribute.
     * 
     * Template types must be acquired/released. Non-template types must be created and then
     * checked if they already exist because there is no way to delete an attribute through the API.
     * 
     * @param User $user
     * @param string $integration
     * @param array $attributesRequested
     * @param bool $isNew
     * @return StandardDTO
     */
    public function setAttributes(User $user, string $integration, array $attributesRequested, bool $isNew): StandardDTO
    {
        $checkTokenResponse = $this->checkToken($user);
        if (!$checkTokenResponse->success) {
            return $checkTokenResponse;
        }
        
        $attributeList = collect(config('services.' . $integration . '.attributes'));
        $attributesRequested = collect($attributesRequested);

        $acquireAttributeBody = array();
        $releaseAttributeBody = array();
        $createAttributeBody = array();

        // sort through the attributes to collect the appropriate data for the Exist API calls
        foreach ($attributeList as $attributeDetail) {
            $attribute = $attributesRequested->where('attribute', $attributeDetail['attribute']);
            $check = UserAttribute::where('user_id', $user->id)
                ->where('integration', $integration)
                ->where('attribute', $attributeDetail['attribute']);

            if ($attribute->count() == 1) {
                // only proceed if the user doesn't already have the attribute
                if ($check->count() == 0) {
                    if ($attributeDetail['template']) {
                        array_push($acquireAttributeBody, [
                            'template' => $attributeDetail['attribute'] // Acquire Attribute needs to use the template key
                        ]);
                    } else {
                        array_push($createAttributeBody, [
                            'label' => $attributeDetail['label'], // Create Attribute needs to use the label key
                            'group' => $attributeDetail['group'],
                            'value_type' => $attributeDetail['value_type'],
                            'manual' => false
                        ]);
                    }

                    if ($isNew) {
                        $this->zeroUserData($user, $integration, $attributeDetail['attribute'], config('services.baseDays'));
                    }
                }
            } else {
                // The attribute was not requested to be added. Check if the user already has it.
                if ($check->count() === 1) {
                    if ($attributeDetail['template']) {
                        array_push($releaseAttributeBody, [
                            'name' => $attributeDetail['attribute'] // Release Attribute needs to use the name key
                        ]);
                    }

                    UserData::where('user_id', $user->id)
                        ->where('service', $integration)
                        ->where('attribute', $attributeDetail['attribute'])
                        ->delete();

                    $check->delete();
                }
            }
        }

        // Aquire the official templates
        if (count($acquireAttributeBody) > 0) {
            $acquireAttributeResponse = $this->api->acquireAttribute($user, $acquireAttributeBody);
            if ($acquireAttributeResponse !== null) {
                foreach ($acquireAttributeResponse->success as $success) {
                    UserAttribute::updateOrCreate([
                        'user_id' => $user->id,
                        'integration' => $integration,
                        'attribute' => $success['template']
                    ]);
                }
            } else {
                return new StandardDTO(
                    success: false,
                    message: "Error connecting to Exist"
                );
            }
        }

        // Release the official templates
        if (count($releaseAttributeBody) > 0) {
            $releaseAttributeBody = $this->api->releaseAttribute($user, $releaseAttributeBody);
            if ($releaseAttributeBody === null) {
                return new StandardDTO(
                    success: false,
                    message: "Error connecting to Exist"
                );
            }
        }

        // Create the new custom attributes
        if (count($createAttributeBody) > 0) {
            $createAttributeResponse = $this->api->createAttribute($user, $createAttributeBody);

            if ($createAttributeResponse !== null) {
                foreach ($createAttributeResponse->success as $success) {
                    UserAttribute::updateOrCreate([
                        'user_id' => $user->id,
                        'integration' => $integration,
                        'attribute' => $success['name']
                    ]);
                }

                // If the attribute has been created it will fail when trying to re-add it
                foreach ($createAttributeResponse->failed as $failure) {
                    if ($failure['error_code'] === "exists") {
                        UserAttribute::updateOrCreate([
                            'user_id' => $user->id,
                            'integration' => $integration,
                            'attribute' => $failure['name']
                        ]);
                    }
                }
            } else {
                return new StandardDTO(
                    success: false,
                    message: "Error connecting to Exist"
                );
            }
        }

        return new StandardDTO(
            success: true
        );
    }

    /**
     * Connect to the API to set values on Exist for the user.
     * Method can be either update or increment.
     * 
     * @param User $user
     * @param array $payload
     * @param string $method
     * @return ExistStatusDTO
     */
    public function setAttributeValue(User $user, array $payload, string $method = "increment"): ?ExistStatusDTO
    {
        $checkTokenResponse = $this->checkToken($user);
        if (!$checkTokenResponse->success) {
            return null;
        }

        if ($method != "increment" && $method != "update") {
            $method = "increment";
        }
        
        if ($method == "increment") {
            $attributeStatus = $this->api->incrementAttributeValue($user, $payload);
        } else if ($method == "update") {
            $attributeStatus = $this->api->updateAttributeValue($user, $payload);
        } else {
            $attributeStatus = null;
        }

        return $attributeStatus;
    }

    /**
     * First this clears out any records in teh UserData table for the integration and attribute
     * so that on the next processing of the data, the last records will be resent after the zeroing.
     * 
     * Adds an entry into the UserData table that will zero out the records in Exist for the
     * service and integration for the number of dates.
     * 
     * @param User $user
     * @param string $integration
     * @param string $attribute
     * @param int $days
     * @return StandardDTO
     */
    public function zeroUserData(User $user, string $integration, string $attribute, int $days): StandardDTO
    {
        UserData::where('user_id', $user->id)
            ->where('service', $integration)
            ->where('attribute', $attribute)
            ->delete();
        
        $startDate = date("Y-m-d", strtotime("-$days days"));
        $endDate = date("Y-m-d");

        $iterationDate = strtoTime($startDate);
        while ($iterationDate <= strtotime($endDate)) {

            UserData::create([
                'user_id' => $user->id,
                'service' => $integration,
                'service_id' => 'zero',
                'attribute' => $attribute,
                'date_id' => date("Y-m-d", $iterationDate),
                'value' => 0
            ]);

            $iterationDate = strtotime("1 day", $iterationDate);
        }
        
        return new StandardDTO(
            success: true
        );
    }
    
    /**
     * Loop through the UserData for the user and process the data to Exist.
     * If zero is passed in, the update endpoint will be used.
     * 
     * @param User $user
     * @param string $integration
     * @param bool $zero
     * @return StandardDTO
     */
    public function sendUserData(User $user, string $integration, bool $zero = false): StandardDTO
    {
        $baseUserData = UserData::where('user_id', $user->id)
            ->where('service', $integration)
            ->where('sent_to_exist', 0);

        if ($zero) {
            $userData = $baseUserData->where('service_id', 'zero')
                ->get();
        } else {
            $userData = $baseUserData->get();
        }

        // build the total Payload
        $totalPayload = array();
        foreach ($userData as $data) {
            $value = $data->value;
            if ($integration == "ynab") {
                $value = round($value / 1000, 2);
            }
            array_push($totalPayload, [
                'name' => $data->attribute,
                'date' => $data->date_id,
                'value' => $value
            ]);
        }

        $maxUpdate = config('services.exist.maxUpdate');
        $loops = ceil(sizeof($totalPayload) / $maxUpdate);

        for ($i = 0; $i < $loops; $i++) {
            $payload = array_slice($totalPayload, $i * $maxUpdate, $maxUpdate);

            if ($zero) {
                $status = $this->setAttributeValue($user, $payload, "update");
            } else {
                $status = $this->setAttributeValue($user, $payload, "increment");
            }

            if ($status !== null) {
                foreach ($status->success as $record) {
                    if ($integration == "ynab") {
                        $value = round($record['value'] * 1000, 0);
                    } else {
                        $value = $record['value'];
                    }
                    $baseUserData = UserData::where('user_id', $user->id)
                        ->where('service', $integration)
                        ->where('attribute', $record['name'])
                        ->where('date_id', $record['date'])
                        ->where('value', $value)
                        ->where('sent_to_exist', false);

                    if ($zero) {
                        $baseUserData->where('service_id', 'zero');
                    }

                    $data = $baseUserData->orderBy('id', 'asc')
                        ->first();

                    if ($data !== null) {
                        $responseDate = new Carbon("now", "UTC");

                        $data->sent_to_exist = true;
                        $data->response_date = $responseDate;

                        if ($zero) {
                            $data->response = "Updated to: " . $record['value'];
                        } else {
                            $data->response = "Incremented " . $record['value'] . " to " . $record['current'];
                        }
                        
                        $data->save();
                    }
                }
            }
            
        }
        
        return new StandardDTO(
            success: true
        );
    }

}