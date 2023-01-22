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
use Illuminate\Support\Facades\DB;
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
     * Tokens usually last two hours and with the timing of the processor, it's possible that the token will expire
     * after this call. Subtract 10 minutes from the check time to capture that eventuality.
     * 
     * @param User $user
     * @return StandardDTO
     */
    public function checkToken(User $user): StandardDTO
    {
        $todaysDate = date('Y-m-d H:i:s');
        $checkDate = date('Y-m-d H:i:s', strtotime("-10 minutes", strtotime($user->ynabUser->token_expires)));

        if ($todaysDate >= $checkDate) {
            $refreshTokenResponse = $this->api->refreshToken($user->ynabUser->refresh_token);
            if ($refreshTokenResponse === null) {
                return new StandardDTO(
                    success: false,
                    message: __('app.oAuthRefreshError', ['service' => 'YNAB'])
                );
            }

            YnabUser::find($user->ynabUser->id)
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
                    'category_id' => $category['id'],
                    
                ], [
                    'category_group_name' => $categoryGroupName,
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

    /**
     * Process the Transactions for the User into the user_data
     * 
     * @param User $user
     * @return StandardDTO
     */
    public function processTransactions(User $user): StandardDTO
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

        $transactionResponse = $this->api->getTransactions($user, $startAt->format('Y-m-d'));
        if ($transactionResponse === null) {
            $unauthorizedCount = ServiceLog::where('user_id', $user->id)
                ->where('service', 'ynab')
                ->where('unauthorized', true)
                ->whereNull('message')
                ->count();

            if ($unauthorizedCount > 0) {
                ServiceLog::where('user_id', $user->id)
                    ->where('service', 'ynab')
                    ->where('unauthorized', true)
                    ->whereNull('message')
                    ->update(['message' => 'Authorization revoked']);

                $this->disconnect($user, "Authorization revoked", true);
            }
            
            return new StandardDTO(
                success: false,
                message: __('app.ynabHistoryError')
            );
        }

        foreach ($transactionResponse->data['transactions'] as $transaction) {
            // check the category
            $category = YnabCategory::where('user_id', $user->id)
                ->where('category_id', $transaction['category_id'])
                ->where('deleted_flag', false)
                ->whereNotNull('attribute')
                ->first();

            if ($category !== null) {
                $service_id = $transaction['id'];
                $date_id = $transaction['date'];
                $attribute = $category->attribute;
                $value = $transaction['amount'];
                $deleted = $transaction['deleted'];
                $this->processTransaction($user, $service_id, $attribute, $date_id, $value, $deleted);
            }
        }

        return new StandardDTO(
            success: true
        );
    }

    /**
     * Process the transaction by figuring out if it's already sent or has changed the totals
     * 
     * @param User $user
     * @param string $service_id
     * @param string $attribute
     * @param string $date_id
     * @param float $value
     * @param bool $deleted
     * @return StandardDTO
     */
    private function processTransaction(User $user, string $service_id, string $attribute, string $date_id, float $value, bool $deleted): StandardDTO
    {
        if ($attribute == "money_earned") {
            $conversion = 1;
        } else {
            $conversion = -1;
        }
        $value = $value * $conversion;

        if (!$deleted) {
            $dataCheck = DB::table('user_data')
                ->selectRaw('date_id, MAX(service_id2) AS id2, SUM(value) AS totalValue')
                ->where('user_id', $user->id)
                ->where('service', 'ynab')
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
        } else {
            $record = DB::table('user_data')
                ->selectRaw('date_id, MAX(service_id2) AS id2, SUM(value) AS totalValue')
                ->where('user_id', $user->id)
                ->where('service', 'ynab')
                ->where('service_id', $service_id)
                ->where('date_id', $date_id)
                ->groupBy('date_id')
                ->first();
            if ($record !== null && $record->totalValue != 0) {
                $this->createUserData($user->id, $service_id, $record->id2 + 1, $attribute, $date_id, 0 - $record->totalValue);
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
            'service' => 'ynab',
            'service_id' => $service_id,
            'service_id2' => $service_id2,
            'attribute' => $attribute,
            'date_id' => $date_id,
            'value' => $value
        ]);
    }

}