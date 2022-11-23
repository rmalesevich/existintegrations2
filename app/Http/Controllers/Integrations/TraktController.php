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

    }

}