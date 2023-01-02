<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

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

    public function logs()
    {
        $userData = DB::table('user_data')
            ->select('service', 'date_id', 'attribute', 'value', 'response as message', 'updated_at')
            ->where('user_id', auth()->user()->id);

        $logs = DB::table('service_logs')
            ->select('service', DB::raw('null as date_id'), DB::raw('null as attribute'), DB::raw('null as value'), 'message', 'updated_at')
            ->where('user_id', auth()->user()->id)
            ->union($userData)
            ->orderBy('updated_at', 'desc')
            ->orderBy('date_id', 'desc')
            ->get();

        return view('logs', [
            'user' => auth()->user(),
            'logs' => $logs
        ]);
    }
}