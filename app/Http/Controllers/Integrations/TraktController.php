<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\TraktUser;
use App\Models\UserAttribute;
use App\Services\ExistService;
use App\Services\TraktService;
use Illuminate\Http\Request;

class TraktController extends Controller
{
    private $trakt;
    private $exist;
    
    public function __construct(TraktService $trakt, ExistService $exist)
    {
        $this->middleware('auth');
        $this->trakt = $trakt;
        $this->exist = $exist;
    }

    /**
     * ROUTE: /services/trakt/connect
     * METHOD: GET
     * 
     * Redirect the user to the OAuth 2.0 URI for the authorization flow for Trakt
     */
    public function connect()
    {
        return redirect($this->trakt->api->getOAuthStarUri());
    }

    /**
     * ROUTE: /services/trakt/connected
     * METHOD: GET
     * 
     * Check on the response from the user in the Authorization request from Trakt and respond accordingly.
     * 
     * @param Request $request
     */
    public function connected(Request $request)
    {
        if (auth()->user()->existUser === null) return redirect()->route('home');
        if (auth()->user()->traktUser !== null) return redirect()->route('home');

        if (request('error') === "access_denied") {
            return redirect()->route('home')
                ->with('errorMessage', __('app.oAuthFlowCanceled', ['service' => 'Trakt']));
        }

        // retrieve the code from Trakt
        $code = $request->get('code');
        if (!isset($code)) return redirect()->route('home');

        $authorizeResponse = $this->trakt->authorize(auth()->user(), $code);
        if ($authorizeResponse->success) {
            $successMessage = __('app.oAuthSuccess', ['service' => 'Trakt']);
        } else {
            $errorMessage = $authorizeResponse->message ?? __('app.unknownError');
        }

        return redirect()->route('home')
            ->with('successMessage', $successMessage ?? null)
            ->with('errorMessage', $errorMessage ?? null);
    }

    /**
     * ROUTE: /services/trakt/disconnect
     * METHOD: DELETE
     * 
     * Calls the disconnect method in the Trakt Service to remove data for this year
     */
    public function disconnect()
    {
        if (auth()->user()->existUser === null || auth()->user()->traktUser === null) return redirect()->route('home');

        $disconnect = $this->trakt->disconnect(auth()->user(), "User Initiated");
        if ($disconnect->success) {
            $successMessage = __('app.serviceDisconnect', ['service' => 'Trakt']);
        } else {
            $errorMessage = $disconnect->message ?? __('app.unknownError');
        }

        return redirect()->route('home')
            ->with('successMessage', $successMessage ?? null)
            ->with('errorMessage', $errorMessage ?? null);
    }

    /**
     * ROUTE: /services/trakt/manage
     * METHOD: GET
     * 
     * Display the manage Trakt service view for the end user
     */
    public function manage()
    {
        if (auth()->user()->traktUser === null) return redirect()->route('home');

        $attributes = array();
        $allAttributes = collect(config('services.trakt.attributes'));
        foreach ($allAttributes as $attribute) {
            if ($attribute['multiple']) {
                // check if this is already configured in a different integration
                $check = UserAttribute::where('user_id', auth()->user()->id)
                    ->where('attribute', $attribute['attribute'])
                    ->whereNot('integration', 'trakt')
                    ->count();
                if ($check == 0) {
                    array_push($attributes, $attribute);
                }
            } else {
                array_push($attributes, $attribute);
            }
        }

        return view('manage.trakt', [
            'user' => auth()->user(),
            'userAttributes' => auth()->user()->attributes->where('integration', 'trakt')->where('user_id', auth()->user()->id),
            'attributes' => collect($attributes)
        ]);
    }

    /**
     * ROUTE: /services/trakt/setAttributes
     * METHOD: POST
     */
    public function setAttributes(Request $request)
    {
        if (auth()->user()->existUser === null || auth()->user()->traktUser === null) return redirect()->route('home');

        $attributes = array();
        foreach (collect(config('services.trakt.attributes')) as $attribute) {
            if ($request[$attribute['attribute']] !== null) {
                array_push($attributes, [
                    'attribute' => $attribute['attribute']
                ]);
            }
        }

        $setAttributesResponse = $this->exist->setAttributes(auth()->user(), 'trakt', $attributes, auth()->user()->traktUser->is_new);
        if ($setAttributesResponse->success) {
            $successMessage = __('app.attributeSuccess');

            if (auth()->user()->traktUser->is_new) {
                TraktUser::where('user_id', auth()->user()->id)
                    ->update([
                        'is_new' => false
                    ]);
            }

        } else {
            $errorMessage = $setAttributesResponse->message ?? __('app.unknownError');
        }

        return redirect()->route('trakt.manage')
            ->with('successMessage', $successMessage ?? null)
            ->with('errorMessage', $errorMessage ?? null); 
    }

    /**
     * ROUTE: /services/trakt/zero
     * METHOD: POST
     */
    public function zero()
    {
        if (auth()->user()->existUser === null || auth()->user()->traktUser === null) return redirect()->route('home');

        $days = config('services.baseDays');
        $userAttributes = UserAttribute::where('user_id', auth()->user()->id)
            ->where('integration', 'trakt')
            ->get();

        foreach ($userAttributes as $attribute) {
            $this->exist->zeroUserData(auth()->user(), 'trakt', $attribute->attribute, $days);
        }

        return redirect()->route('trakt.manage')
            ->with('successMessage', __('app.zeroOutSuccess', ['service' => 'Trakt', 'days' => $days]));
    }

}