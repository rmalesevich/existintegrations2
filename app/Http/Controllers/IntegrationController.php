<?php

namespace App\Http\Controllers;

class IntegrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function home()
    {
        if (!empty(auth()->user()->existUser->id)) {
            // Configure and display the home page for a user already connected to Exist
            return view('home', [
                'user' => auth()->user(),
                'integrations' => collect(config('services.integrations'))
            ]);
        } else {
            // User is not already connected to Exist
            return view('onboard');
        }   
    }

    public function add()
    {       
        return view('add', [
            'user' => auth()->user(),
            'integrations' => collect(config('services.integrations'))
        ]);
    }
}