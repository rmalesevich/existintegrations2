<?php

namespace App\Services;

use App\Models\ServiceLog;
use App\Models\TraktUser;
use App\Models\User;
use App\Models\UserAttribute;
use App\Models\UserData;
use App\Objects\StandardDTO;
use App\Services\ApiIntegrations\TraktApiService;
use Carbon\Carbon;
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

        ServiceLog::create([
            'user_id' => $user->id,
            'service' => 'trakt',
            'message' => 'Connected to Trakt'
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
     * @param bool $unauthorized
     * @return StandardDTO
     */
    public function disconnect(User $user, string $trigger = "", bool $unauthorized = false): StandardDTO
    {
        UserData::where('user_id', $user->id)
            ->where('service', 'trakt')
            ->delete();
        UserAttribute::where('user_id', $user->id)
            ->where('integration', 'trakt')
            ->delete();
        TraktUser::where('user_id', $user->id)
            ->delete();
        
        Log::info(sprintf("TRAKT DISCONNECT: User ID %s via trigger %s", $user->id, $trigger));

        if (!$unauthorized) {
            ServiceLog::create([
                'user_id' => $user->id,
                'service' => 'trakt',
                'message' => 'Disconnected from Trakt. Via trigger ' . $trigger
            ]);
        }
        
        return new StandardDTO(
            success: true
        );
    }

    /**
     * Token the Token for the user to ensure it's still valid. If required, refresh the token from Trakt.
     * Trakt tokens last for 365 days, but just in case there is a weird case of timing this will refresh
     * the token 7 days before it expires.
     * 
     * @param User $user
     * @return StandardDTO
     */
    public function checkToken(User $user): StandardDTO
    {
        $todaysDate = date('Y-m-d H:i:s');
        $checkDate = date('Y-m-d H:i:s', strtotime("-7 days", strtotime($user->traktUser->token_expires)));

        if ($todaysDate >= $checkDate) {
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

    /**
     * Load the History from Trakt and then get the runtime to add to the
     * UserData table
     * 
     * @param User $user
     * @return StandardTO
     */
    public function processHistory(User $user): StandardDTO
    {
        $userToken = $this->checkToken($user);
        if ($userToken->success) {
            $user = User::find($user->id);
        } else {
            return new StandardDTO(
                success: false
            );
        }
        
        $days = config('services.baseDays') * -1;

        $startAt = new Carbon();
        $startAt->addDays($days);
        $endAt = new Carbon();
        $endAt->addDays(1);

        $historyResponse = $this->api->getHistory($user, $startAt, $endAt);
        if ($historyResponse === null) {
            $unauthorizedCount = ServiceLog::where('user_id', $user->id)
                ->where('service', 'trakt')
                ->where('unauthorized', true)
                ->whereNull('message')
                ->count();

            if ($unauthorizedCount > 0) {
                ServiceLog::where('user_id', $user->id)
                    ->where('service', 'trakt')
                    ->where('unauthorized', true)
                    ->whereNull('message')
                    ->update(['message' => 'Authorization revoked']);

                $this->disconnect($user, "Authorization revoked", true);
            }
            
            return new StandardDTO(
                success: false,
                message: __('app.traktHistoryError')
            );
        }

        $userAttributes = UserAttribute::where('user_id', $user->id)
            ->where('integration', 'trakt')
            ->get();

        foreach ($historyResponse->data as $history) {
            if ($history->type == "episode" && $userAttributes->where('attribute', 'tv_min')->count() != 1) continue;
            if ($history->type == "movie" && $userAttributes->where('attribute', 'watching_movies')->count() != 1) continue;
            
            $serviceId = $history->id;

            // if the record has already been processed, then ignore the check of the times to avoid unnecessary API calls to Trakt
            $existed = UserData::where('user_id', $user->id)
                ->where('service', 'trakt')
                ->where('service_id', $serviceId)
                ->count();
            if ($existed == 1) continue;

            $historyDT = new Carbon($history->watched_at, "UTC");
            $historyDT->setTimezone($user->existUser->timezone);
            $dateId = $historyDT->format('Y-m-d');

            $value = 0;

            if ($history->type == "movie") {
                $attribute = "watching_movies";
                if ($history->movie['ids']['trakt'] !== null) {
                    $movieResponse = $this->api->getMovie($history->movie['ids']['trakt']);

                    if ($movieResponse !== null) {
                        $value = $movieResponse->runtime;
                    }
                }
            } else if ($history->type == "episode") {
                $attribute = "tv_min";
                if ($history->show['ids']['trakt'] !== null) {
                    $episodeResponse = $this->api->getEpisode($history->show['ids']['trakt'], $history->episode['season'], $history->episode['number']);

                    if ($episodeResponse !== null) {
                        $value = $episodeResponse->runtime;
                    }
                }
            }

            UserData::updateOrCreate([
                'user_id' => $user->id,
                'service' => 'trakt',
                'service_id' => $serviceId,
                'attribute' => $attribute,
                'date_id' => $dateId
            ], [
                'value' => $value
            ]);
            
        }
        
        return new StandardDTO(
            success: true
        );
    }

}