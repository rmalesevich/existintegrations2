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

    'roadmapUri' => 'https://changemap.co/exist-integrations/exist-integrations/',

    'baseDays' => env('BASE_DAYS'),
    'logDaysKept' => env('LOG_DAYS_KEPT'),

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
        'key' => 'whatpulse',
        'baseUri' => 'https://api.whatpulse.org',
        'attributes' => [
            [
                'attribute' => 'keystrokes',
                'template' => 'keystrokes',
                'label' => 'Keystrokes',
                'group' => 'productivity',
                'value_type' => 3
            ], [
                'attribute' => 'mouse_clicks',
                'template' => null,
                'label' => 'Mouse Clicks',
                'group' => 'productivity',
                'value_type' => 0
            ], [
                'attribute' => 'download_mb',
                'template' => null,
                'label' => 'Download MB',
                'group' => 'productivity',
                'value_type' => 0
            ], [
                'attribute' => 'upload_mb',
                'template' => null,
                'label' => 'Upload MB',
                'group' => 'productivity',
                'value_type' => 0
            ]
        ]
    ],

    // Configuration related to the integration with Trakt
    'trakt' => [
        'key' => 'trakt',
        'authUri' => 'https://trakt.tv/oauth/authorize',
        'tokenUri' => 'https://trakt.tv/oauth/token',
        'baseUri' => 'https://api.trakt.tv',
        'attributes' => [
            [
                'attribute' => 'tv_min',
                'template' => 'tv_min',
                'label' => 'Watching TV',
                'group' => 'media',
                'value_type' => 3,
                'multiple' => true
            ], [
                'attribute' => 'watching_movies',
                'template' => null,
                'label' => 'Watching Movies',
                'group' => 'media',
                'value_type' => 3,
                'multiple' => true
            ]
        ]
    ],

    // Configuration related to the integration with Trakt
    'ynab' => [
        'key' => 'ynab',
        'authUri' => 'https://app.youneedabudget.com/oauth/authorize',
        'tokenUri' => 'https://app.youneedabudget.com/oauth/token',
        'baseUri' => 'https://api.youneedabudget.com/v1',
        'attributes' => [
            [
                'attribute' => 'money_spent',
                'template' => 'money_spent',
                'label' => 'Money spent',
                'group' => 'finance',
                'value_type' => 1
            ], [
                'attribute' => 'money_earned',
                'template' => null,
                'label' => 'Money earned',
                'group' => 'finance',
                'value_type' => 1
            ], [
                'attribute' => 'money_saved',
                'template' => null,
                'label' => 'Money saved',
                'group' => 'finance',
                'value_type' => 1
            ]
        ]
    ],

    // Configuration related to the integration with Toggl Track
    'toggl' => [
        'key' => 'toggl',
        'baseUri' => 'https://api.track.toggl.com/api/v8',
        'reportsBaseUri' => 'https://api.track.toggl.com/reports/api/v2',
        'userAgent' => 'existintegrations',
        'attributes' => [
            [
                'attribute' => 'gaming_min',
                'template' => 'gaming_min',
                'label' => 'Watching TV',
                'group' => 'media',
                'value_type' => 3
            ], [
                'attribute' => 'productive_min',
                'template' => 'productive_min',
                'label' => 'Productive time',
                'group' => 'productivity',
                'value_type' => 3
            ], [
                'attribute' => 'neutral_min',
                'template' => 'neutral_min',
                'label' => 'Neutral time',
                'group' => 'productivity',
                'value_type' => 3
            ], [
                'attribute' => 'distracting_min',
                'template' => 'distracting_min',
                'label' => 'Distracting time',
                'group' => 'productivity',
                'value_type' => 3
            ], [
                'attribute' => 'tv_min',
                'template' => 'tv_min',
                'label' => 'Watching TV',
                'group' => 'media',
                'value_type' => 3,
                'multiple' => true
            ], [
                'attribute' => 'watching_movies',
                'template' => null,
                'label' => 'Watching Movies',
                'group' => 'media',
                'value_type' => 3,
                'multiple' => true
            ], 
        ]
    ],

    // Array with all of the officially supported integrations
    'integrations' => [
        [
            'service' => 'whatpulse',
            'userMethod' => 'whatPulseUser',
            'outputName' => 'WhatPulse',
            'logo' => '/images/whatpulse.png',
            'description' => 'WhatPulse measures your computer usage through keyboard/mouse usage, bandwidth, and uptime. This data is available through their API so it can be mapped to Exist attributes and populated through Exist Integrations.',
            'enabled' => true
        ], [
            'service' => 'trakt',
            'userMethod' => 'traktUser',
            'outputName' => 'Trakt',
            'logo' => '/images/trakt.png',
            'description' => 'Trakt is a platform that keeps track of the TV shows and movies you watch. Your watch history can be pulled through their API and mapped to total media time attributes on Exist through Exist Integrations',
            'enabled' => true
        ], [
            'service' => 'ynab',
            'userMethod' => 'ynabUser',
            'outputName' => 'YNAB',
            'logo' => '/images/ynab.svg',
            'description' => 'You Need a Budget (YNAB) categorizes your money input/output into monthly budgets. Your transaction totals can be pulled into financial attributes on Exist through Exist Integrations.',
            'enabled' => true
        ], [
            'service' => 'toggl',
            'userMethod' => 'togglUser',
            'outputName' => 'Toggl Track',
            'logo' => '/images/toggl.png',
            'description' => 'Toggl Track is a simple and powerful time tracking tool so you can understand how you spend your time. Time entries within projects can be pulled into various attributes on Exist through Exist Integrations.',
            'enabled' => true
        ]
    ]

];
