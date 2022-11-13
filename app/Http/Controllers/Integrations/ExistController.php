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
     * METHOD: GET
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
     * METHOD: GET
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
        if (!isset($code)) return redirect()->route('home');

        $authorizeResponse = $this->exist->authorize(auth()->user(), $code);
        if ($authorizeResponse->success) {
            $successMessage = "Exist Integrations has successfully connected to your Exist account";
        } else {
            $errorMessage = $authorizeResponse->message ?? "Unknown error";
        }

        return redirect()->route('home')
            ->with('successMessage', $successMessage ?? null)
            ->with('errorMessage', $errorMessage ?? null);
    }

    /**
     * ROUTE: /services/exist/disconnect
     * METHOD: DELETE
     * 
     * Calls the disconnect method in the Exist Service to remove any data for this user
     */
    public function disconnect()
    {
        if (auth()->user()->existUser === null) return redirect()->route('home');

        $disconnect = $this->exist->disconnect(auth()->user(), "User Initiated");
        if ($disconnect->success) {
            $successMessage = "Exist Integrations has been successfully disconnected from your Exist account";
        } else {
            $errorMessage = $disconnect->message ?? "Unknown error";
        }

        return redirect()->route('home')
            ->with('successMessage', $successMessage ?? null)
            ->with('errorMessage', $errorMessage ?? null);
    }

    /**
     * ROUTE: /services/exist/manage
     * METHOD: GET
     * 
     * Display the manage Exist service view for the end user
     */
    public function manage()
    {
        if (auth()->user()->existUser === null) return redirect()->route('home');

        return view('manage.exist', [
            'user' => auth()->user()
        ]);
    }

    /** 
     * ROUTE: /services/exist/updateAccountProfile
     * METHOD: POST
     * 
     * Retrieve the Account Profile from Exist and persist the data in the exist_users table.
     */
    public function updateAccountProfile()
    {
        if (auth()->user()->existUser === null) return redirect()->route('home');

        $updateAccountProfileResponse = $this->exist->updateAccountProfile(auth()->user());

        if ($updateAccountProfileResponse->success) {
            $successMessage = "Exist Integrations has pulled your latest Account Profile from Exist";
        } else {
            $errorMessage = $updateAccountProfileResponse->message ?? "Unknown error";
        }

        return redirect()->route('exist.manage')
            ->with('successMessage', $successMessage ?? null)
            ->with('errorMessage', $errorMessage ?? null);
    }
}