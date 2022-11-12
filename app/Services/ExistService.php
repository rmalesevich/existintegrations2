<?php

namespace App\Services;

use App\Models\ExistUser;
use App\Models\User;
use App\Models\UserAttribute;
use App\Objects\StandardDTO;
use App\Services\ApiIntegrations\ExistApiService;
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
                message: "Failed to exchange the OAuth code for the Token."
            );
        }

        ExistUser::create([
            'user_id' => $user->id,
            'access_token' => $oauthTokenResponse->access_token,
            'refresh_token' => $oauthTokenResponse->refresh_token,
            'token_expires' => date('Y-m-d H:i:s', (time() + $oauthTokenResponse->expires_in))
        ]);

        $accountProfileResponse = $this->api->getAccountProfile($user);
        if ($accountProfileResponse === null) {
            ExistUser::find($user->existUser->id)->delete();
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
     * 
     * @param User $user
     * @return StandardDTO
     */
    public function checkToken(User $user): StandardDTO
    {
        $todaysDate = date('Y-m-d H:i:s');

        if ($todaysDate >= $user->existUser->token_expires) {
            $refreshTokenResponse = $this->api->refreshToken($user->existUser->refresh_token);
            if ($refreshTokenResponse === null) {
                return new StandardDTO(
                    success: false,
                    message: "Failed to refresh the Access Token from Exist"
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
     * @return StandardDTO
     */
    public function setAttributes(User $user, string $integration, array $attributesRequested): StandardDTO
    {
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
                            'value_type' => $attributeDetail['value_type']
                        ]);
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
                        'integration' => 'whatpulse',
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
                        'integration' => 'whatpulse',
                        'attribute' => $success['name']
                    ]);
                }
    
                // If the attribute has been created it will fail when trying to re-add it
                foreach ($createAttributeResponse->failed as $failure) {
                    if ($failure['error_code'] === "exists") {
                        UserAttribute::updateOrCreate([
                            'user_id' => $user->id,
                            'integration' => 'whatpulse',
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

}