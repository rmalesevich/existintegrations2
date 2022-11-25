<?php

namespace App\Services;

use App\Models\TraktUser;
use App\Models\User;
use App\Models\UserAttribute;
use App\Models\UserData;
use App\Objects\StandardDTO;
use App\Services\ApiIntegrations\TraktApiService;
use Illuminate\Support\Facades\Log;

class TraktService
{
    public $api;

    public function __construct(TraktApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Complete the OAuth authentication workflow with Trakt
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

        TraktUser::create([
            'user_id' => $user->id,
            'access_token' => $oauthTokenResponse->access_token,
            'refresh_token' => $oauthTokenResponse->refresh_token,
            'token_expires' => date('Y-m-d H:i:s', (time() + $oauthTokenResponse->expires_in))
        ]);
        $user = User::find($user->id);

        $accountProfileResponse = $this->api->getAccountProfile($user);
        if ($accountProfileResponse === null) {
            TraktUser::find($user->traktUser->id)->delete();
            return new StandardDTO(
                success: false,
                message: __('app.accountProfileAPIFail', ['service' => 'Trakt'])
            );
        }

        TraktUser::find($user->traktUser->id)
            ->update([
                'username' => $accountProfileResponse->username
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
            ->where('service', 'trakt')
            ->delete();
        UserAttribute::where('user_id', $user->id)
            ->where('integration', 'trakt')
            ->delete();
        TraktUser::where('id', $user->traktUser->id)
            ->delete();
        
        Log::info(sprintf("TRAKT DISCONNECT: User ID %s via trigger %s", $user->id, $trigger));
        
        return new StandardDTO(
            success: true
        );
    }

    /**
     * Token the Token for the user to ensure it's still valid. If required, refresh the token from Trakt.
     * 
     * @param User $user
     * @return StandardDTO
     */
    public function checkToken(User $user): StandardDTO
    {
        $todaysDate = date('Y-m-d H:i:s');

        if ($todaysDate >= $user->traktUser->token_expires) {
            $refreshTokenResponse = $this->api->refreshToken($user->traktUser->refresh_token);
            if ($refreshTokenResponse === null) {
                return new StandardDTO(
                    success: false,
                    message: __('app.oAuthRefreshError', ['service' => 'Trakt'])
                );
            }

            TraktUser::find($user->traktUser->id)
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

}