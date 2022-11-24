<?php

namespace App\Services;

use App\Models\TraktUser;
use App\Models\User;
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

}