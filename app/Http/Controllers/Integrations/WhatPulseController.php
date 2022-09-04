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
     * ROUTE: /services/whatpulse/manage
     * METHOD: GET
     */
    public function manage()
    {
        dd("To implement");
    }
}