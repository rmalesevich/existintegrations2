<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\UserAttribute;
use App\Services\ExistService;
use App\Services\YnabService;
use Illuminate\Http\Request;

class YnabController extends Controller
{
    private $ynab;
    private $exist;
    
    public function __construct(YnabService $ynab, ExistService $exist)
    {
        $this->middleware('auth');
        $this->ynab = $ynab;
        $this->exist = $exist;
    }

    /**
     * ROUTE: /services/ynab/connect
     * METHOD: GET
     * 
     * Redirect the user to the OAuth 2.0 URI for the authorization flow for YNAB
     */
    public function connect()
    {
        return redirect($this->ynab->api->getOAuthStarUri());
    }

    /**
     * ROUTE: /services/ynab/connected
     * METHOD: GET
     * 
     * Check on the response from the user in the Authorization request from YNAB and respond accordingly.
     * 
     * @param Request $request
     */
    public function connected(Request $request)
    {
        if (auth()->user()->existUser === null) return redirect()->route('home');
        if (auth()->user()->ynabUser !== null) return redirect()->route('home');

        if (request('error') === "access_denied") {
            return redirect()->route('home')
                ->with('errorMessage', __('app.oAuthFlowCanceled', ['service' => 'YNAB']));
        }

        // retrieve the code from YNAB
        $code = $request->get('code');
        if (!isset($code)) return redirect()->route('home');

        $authorizeResponse = $this->ynab->authorize(auth()->user(), $code);
        if ($authorizeResponse->success) {
            $successMessage = __('app.oAuthSuccess', ['service' => 'YNAB']);
        } else {
            $errorMessage = $authorizeResponse->message ?? __('app.unknownError');
        }

        return redirect()->route('home')
            ->with('successMessage', $successMessage ?? null)
            ->with('errorMessage', $errorMessage ?? null);
    }

    /**
     * ROUTE: /services/ynab/disconnect
     * METHOD: DELETE
     * 
     * Calls the disconnect method in the YNAB Service to remove data for this year
     */
    public function disconnect()
    {
        if (auth()->user()->existUser === null || auth()->user()->ynabUser === null) return redirect()->route('home');

        $disconnect = $this->ynab->disconnect(auth()->user(), "User Initiated");
        if ($disconnect->success) {
            $successMessage = __('app.serviceDisconnect', ['service' => 'YNAB']);
        } else {
            $errorMessage = $disconnect->message ?? __('app.unknownError');
        }

        return redirect()->route('home')
            ->with('successMessage', $successMessage ?? null)
            ->with('errorMessage', $errorMessage ?? null);
    }

    /**
     * ROUTE: /services/ynab/manage
     * METHOD: GET
     * 
     * Display the manage Trakt service view for the end user
     */
    public function manage()
    {
        if (auth()->user()->ynabUser === null) return redirect()->route('home');

        return view('manage.ynab', [
            'user' => auth()->user(),
            'userAttributes' => auth()->user()->attributes->where('integration', 'ynab')->where('user_id', auth()->user()->id),
            'attributes' => collect(config('services.ynab.attributes'))
        ]);
    }

}