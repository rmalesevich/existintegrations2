<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Configuration related to the integration with Exist
    'exist' => [
        'key' => 'exist',
        'authUri' => 'https://exist.io/oauth2/authorize',
        'tokenUri' => 'https://exist.io/oauth2/access_token',
        'baseUri' => 'https://exist.io/api/2',
        'scope' => 'finance_write+manual_write+media_write+productivity_write',
        'maxUpdate' => 35
    ],

    // Configurations related to the integration with WhatPulse
    'whatpulse' => [
        'baseUri' => 'https://api.whatpulse.org'
    ],

    // Array with all of the officially supported integrations
    'integrations' => [
        [
            'service' => 'whatpulse',
            'enabled' => true
        ]
    ]

];
