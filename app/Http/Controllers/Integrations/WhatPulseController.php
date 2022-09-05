<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Services\WhatPulseService;
use Illuminate\Http\Request;

class WhatPulseController extends Controller
{
    private $whatpulse;

    public function __construct(WhatPulseService $whatpulse)
    {
        $this->middleware('auth');
        $this->whatpulse = $whatpulse;
    }

    /**
     * ROUTE: /services/whatpulse/connect
     * METHOD: POST
     */
    public function connect(Request $request)
    {
        if (auth()->user()->existUser === null) return redirect()->route('home');

        if (auth()->user()->whatpulseUser != null) {
            return redirect()->route('home')
                ->with('errorMessage', 'A WhatPulse account is already connected to your user');
        }

        $this->validate($request, [
            'whatpulseAccountName' => 'required'
        ], [
            'whatpulseAccountName.required' => 'WhatPulse Account Name is required'
        ]);

        $whatpulseAccountName = $request->whatpulseAccountName;

        $connect = $this->whatpulse->connect(auth()->user(), $whatpulseAccountName);

        if ($connect->success) {
            return redirect()->route('whatpulse.manage');
        } else {
            return redirect()->route('add')
                ->with('errorMessage', $connect->message);
        }
    }

    /**
     * ROUTE: /services/whatpulse/disconnect
     * METHOD: DELETE
     * 
     * Calls the disconnect method in the WhatPulse Service to remove data for this year
     */
    public function disconnect()
    {
        if (auth()->user()->existUser === null || auth()->user()->whatPulseUser === null) return redirect()->route('home');

        $disconnect = $this->whatpulse->disconnect(auth()->user(), "User Initiated");
        if ($disconnect->success) {
            $successMessage = "Exist Integrations has been successfully disconnected from your WhatPulse account";
        } else {
            $errorMessage = $disconnect->message ?? "Unknown error";
        }

        return redirect()->route('home')
            ->with('successMessage', $successMessage ?? null)
            ->with('errorMessage', $errorMessage ?? null);
    }

    /**
     * ROUTE: /services/whatpulse/manage
     * METHOD: GET
     */
    public function manage()
    {
        if (auth()->user()->existUser === null || auth()->user()->whatPulseUser === null) return redirect()->route('home');

        return view('manage.whatpulse', [
            'user' => auth()->user(),
            'userAttributes' => auth()->user()->attributes->where('integration', 'whatpulse'),
            'attributes' => collect(config('services.whatpulse.attributes'))
        ]);
    }

    /**
     * ROUTE: /services/whatpulse/setAttributes
     * METHOD: POST
     */
    public function setAttributes(Request $request)
    {
        if (auth()->user()->existUser === null || auth()->user()->whatPulseUser === null) return redirect()->route('home');

        $attributes = array();
        foreach (collect(config('services.whatpulse.attributes')) as $attribute) {
            if ($request[$attribute['attribute']] !== null) {
                array_push($attributes, $attribute['attribute']);
            }
        }

        $setAttributesResponse = $this->whatpulse->setAttributes(auth()->user(), $attributes);

        if ($setAttributesResponse->success) {
            $successMessage = "Exist Integrations has set up your attributes and requested an update of your data";
        } else {
            $errorMessage = $setAttributesResponse->message ?? "Unknown error";
        }

        return redirect()->route('whatpulse.manage')
            ->with('successMessage', $successMessage ?? null)
            ->with('errorMessage', $errorMessage ?? null);
            
    }
}