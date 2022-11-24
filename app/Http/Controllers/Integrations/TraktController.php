<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Services\ExistService;
use App\Services\TraktService;
use Illuminate\Http\Request;

class TraktController extends Controller
{
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
     * ROUTE: /services/trakt/manage
     * METHOD: GET
     * 
     * Display the manage Trakt service view for the end user
     */
    public function manage()
    {
        if (auth()->user()->traktUser === null) return redirect()->route('home');

        return view('manage.trakt', [
            'user' => auth()->user()
        ]);
    }

}