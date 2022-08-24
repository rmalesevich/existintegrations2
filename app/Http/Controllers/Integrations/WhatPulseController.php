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

    public function test(Request $request)
    {
        return $this->whatpulse->connect("ryanmal");
    }
}