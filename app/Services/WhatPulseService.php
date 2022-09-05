<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAttribute;
use App\Models\WhatPulseUser;
use App\Objects\StandardDTO;
use App\Services\ApiIntegrations\ExistApiService;
use App\Services\ApiIntegrations\WhatPulseApiService;
use Illuminate\Support\Facades\Log;

class WhatPulseService
{
    public $api;

    public function __construct(WhatPulseApiService $api, ExistApiService $exist)
    {
        $this->api = $api;
        $this->exist = $exist;
    }

    /**
     * Connect the WhatPulse Account Name to this Exist User
     * 
     * @param User $user
     * @param string $accountName
     * @return StandardDTO
     */
    public function connect(User $user, $accountName): StandardDTO
    {
        if (WhatPulseUser::where('account_name', $accountName)->exists()) {
            return new StandardDTO(
                success: false,
                message: "WhatPulse account is already connected to Exist Integrations"
            );
        }

        $userDetailsResponse = $this->api->getUserDetails($accountName);

        if ($userDetailsResponse === null || $userDetailsResponse->error !== null) {
            return new StandardDTO(
                success: false,
                message: "No details were retrieved for this user from WhatPulse"
            );
        }

        WhatPulseUser::create([
            'user_id' => $user->id,
            'account_name' => $userDetailsResponse->AccountName
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
        WhatPulseUser::where('id', $user->whatPulseUser->id)->delete();
        UserAttribute::where('user_id', $user->id)
            ->where('integration', 'whatpulse')
            ->delete();

        Log::info(sprintf("EXIST DISCONNECT: User ID %s via trigger %s", $user->id, $trigger));
        
        return new StandardDTO(
            success: true
        );
    }

    /**
     * Connect these attributes to Exist and store in the database
     * 
     * @param User $user
     * @param array $attributes
     * @return StandardDTO
     */
    public function setAttributes(User $user, array $attributes): StandardDTO
    {
        $attributeList = collect(config('services.whatpulse.attributes'));
        $acquireAttributeBody = array();
        $releaseAttributeBody = array();
        $createAttributeBody = array();

        $attributes = collect($attributes);

        // collect the attributes into the appropriate bodies for the Exist API calls
        foreach ($attributeList as $attributeDetail) {
            $attribute = $attributes->where('attribute', $attributeDetail['attribute']);

            if ($attribute->count() === 1) {
                if (UserAttribute::where('user_id', $user->id)->where('integration', 'whatpulse')->where('attribute', $attributeDetail['attribute'])->count() === 0) {
                    if ($attributeDetail['template']) {
                        array_push($acquireAttributeBody, [
                            'template' => $attributeDetail['attribute']
                        ]);
                    } else {
                        array_push($createAttributeBody, [
                            'label' => $attributeDetail['label'],
                            'group' => $attributeDetail['group'],
                            'value_type' => $attributeDetail['value_type']
                        ]);
                    }
                }
            } else {
                $check = UserAttribute::where('user_id', $user->id)
                    ->where('integration', 'whatpulse')
                    ->where('attribute', $attributeDetail['attribute']);

                if ($check->count() === 1) {
                    if ($attributeDetail['template']) {
                        array_push($releaseAttributeBody, [
                            'name' => $attributeDetail['attribute']
                        ]);
                    }
                    $check->delete();
                }
            }
        }

        // Aquire the official templates
        if (count($acquireAttributeBody) > 0) {
            $acquireAttributeResponse = $this->exist->acquireAttribute($user, $acquireAttributeBody);
            foreach ($acquireAttributeResponse->success as $success) {
                UserAttribute::updateOrCreate([
                    'user_id' => $user->id,
                    'integration' => 'whatpulse',
                    'attribute' => $success['template']
                ]);
            }
        }
        
        // Release the official templates
        if (count($releaseAttributeBody) > 0) {
            $releaseAttributeBody = $this->exist->releaseAttribute($user, $releaseAttributeBody);
        }

        // Create the new custom attributes
        if (count($createAttributeBody) > 0) {
            $createAttributeResponse = $this->exist->createAttribute($user, $createAttributeBody);

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
        }
        
        return new StandardDTO(
            success: true
        );
    }

}