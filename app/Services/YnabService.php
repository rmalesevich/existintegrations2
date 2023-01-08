<?php

namespace App\Services;

use App\Models\ServiceLog;
use App\Models\User;
use App\Models\UserAttribute;
use App\Models\UserData;
use App\Models\YnabCategory;
use App\Models\YnabUser;
use App\Objects\StandardDTO;
use App\Services\ApiIntegrations\YnabApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class YnabService
{
    public $api;

    public function __construct(YnabApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Complete the OAuth authentication workflow with YNAB
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

        YnabUser::create([
            'user_id' => $user->id,
            'access_token' => $oauthTokenResponse->access_token,
            'refresh_token' => $oauthTokenResponse->refresh_token,
            'token_expires' => date('Y-m-d H:i:s', (time() + $oauthTokenResponse->expires_in))
        ]);
        $user = User::find($user->id);

        $userResponse = $this->api->getUser($user);
        if ($userResponse === null) {
            YnabUser::find($user->ynabUser->id)->delete();
            return new StandardDTO(
                success: false,
                message: __('app.accountProfileAPIFail', ['service' => 'YNAB'])
            );
        }

        YnabUser::find($user->ynabUser->id)
            ->update([
                'username' => $userResponse->id
            ]);

        $categoryResponse = $this->processCategories($user);
        if (!$categoryResponse->success) {
            YnabUser::find($user->ynabUser->id)->delete();
            return new StandardDTO(
                success: false,
                message: __('app.categoryAPIFail', ['category' => 'Categories', 'service' => 'YNAB'])
            );
        }

        ServiceLog::create([
            'user_id' => $user->id,
            'service' => 'ynab',
            'message' => 'Connected to YNAB'
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
            ->where('service', 'ynab')
            ->delete();
        UserAttribute::where('user_id', $user->id)
            ->where('integration', 'ynab')
            ->delete();
        YnabCategory::where('user_id', $user->id)
            ->delete();
        YnabUser::where('id', $user->ynabUser->id)
            ->delete();
        
        Log::info(sprintf("YNAB DISCONNECT: User ID %s via trigger %s", $user->id, $trigger));

        if (!$unauthorized) {
            ServiceLog::create([
                'user_id' => $user->id,
                'service' => 'ynab',
                'message' => 'Disconnected from YNAB. Via trigger ' . $trigger
            ]);
        }
        
        return new StandardDTO(
            success: true
        );
    }

    /**
     * Token the Token for the user to ensure it's still valid. If required, refresh the token from YNAB.
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
                    message: __('app.oAuthRefreshError', ['service' => 'YNAB'])
                );
            }

            YnabUser::find($user->traktUser->id)
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
     * Process the Categories from YNAB into the database
     * 
     * @param User $user
     * @return StandardDTO
     */
    public function processCategories(User $user): StandardDTO
    {
        $userToken = $this->checkToken($user);
        if ($userToken->success) {
            $user = User::find($user->id);
        } else {
            return new StandardDTO(
                success: false
            );
        }
        
        // get the categories from YNAB
        $categoryResponse = $this->api->getCategories($user);
        if ($categoryResponse === null || $categoryResponse->data === null) {
            return new StandardDTO(
                success: false,
                message: __('app.categoryAPIFail', ['category' => 'Categories', 'service' => 'YNAB'])
            );
        }

        foreach($categoryResponse->data['category_groups'] as $categoryGroup) {
            $categoryGroupName = $categoryGroup['name'];

            foreach ($categoryGroup['categories'] as $category) {
                YnabCategory::updateOrCreate([
                    'user_id' => $user->id,
                    'category_group_name' => $categoryGroupName,
                    'category_id' => $category['id'],
                    'category_name' => $category['name'],
                    'deleted_flag' => $category['deleted']
                ]);
            }
        }

        return new StandardDTO(
            success: true
        );
    }

    /**
     * Update the Category in the database based on the user's management of the YNAB configuration
     * 
     * @param User $user
     * @param string $categoryId
     * @param string $attribute
     * @return StandardDTO
     */
    public function updateCategory(User $user, string $categoryId, string $attribute): StandardDTO
    {
        if ($attribute == __('app.dropdownIgnore') ) {
            $attribute = null;
        }
        
        YnabCategory::where('user_id', $user->id)
            ->where('category_id', $categoryId)
            ->update([
                'attribute' => $attribute
            ]);

        return new StandardDTO(
            success: true
        );
    }

}