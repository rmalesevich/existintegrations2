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
            
            if (env('MESSAGE_CONTENT') !== null && env('MESSAGE_CONTENT') != "") {
                $messageSet = true;
                $message = env('MESSAGE_CONTENT');
            } else {
                $messageSet = false;
                $message = null;
            }
            
            // Configure and display the home page for a user already connected to Exist
            return view('home', [
                'user' => auth()->user(),
                'integrations' => collect(config('services.integrations')),
                'messageSet' => $messageSet,
                'message' => $message
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