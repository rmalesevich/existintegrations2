<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        if (!empty($user->existUser->id)) {
            // Configure and display the home page for a user already connected to Exist
            return view('home');
        } else {
            // User is not already connected to Exist
            return view('onboard');
        }
        
    }
}