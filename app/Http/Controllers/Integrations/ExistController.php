<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Services\ExistService;
use Illuminate\Http\Request;

class ExistController extends Controller
{
    private $exist;

    public function __construct(ExistService $exist)
    {
        $this->middleware('auth');
        $this->exist = $exist;
    }

    /**
     * ROUTE: /services/exist/connect
     * 
     * Redirect the user to the OAuth 2.0 URI for the authorization flow for Exist
     */
    public function connect()
    {
        // TO DO: ensure we don't already have an Exist User for this User

        return redirect($this->exist->api->getOAuthStarUri());
    }

    /**
     * ROUTE: /services/exist/connected
     * 
     * Check on the response from the user in the Authorization request from Exist and respond accordingly.
     * 
     * @param Request $request
     */
    public function connected(Request $request)
    {
        if (request('error') === "access_denied") {
            return redirect()->route('home')
                ->with('errorMessage', 'Exist authorization flow was canceled.');
        }

        // retrieve the code from Exist
        $code = $request->get('code');

        echo $code;
    }
}