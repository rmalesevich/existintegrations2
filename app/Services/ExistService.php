<?php

namespace App\Services;

use App\Models\ExistUser;
use App\Models\User;
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
        ExistUser::find($user->existUser->id)->delete();

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

}