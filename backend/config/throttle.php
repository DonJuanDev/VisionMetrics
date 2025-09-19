<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    */

    'api_requests' => env('THROTTLE_API_REQUESTS', 100),
    'login_attempts' => env('THROTTLE_LOGIN_ATTEMPTS', 5),
    
    /*
    |--------------------------------------------------------------------------
    | Custom Rate Limits
    |--------------------------------------------------------------------------
    */

    'limits' => [
        'webhook' => [
            'attempts' => 1000,
            'decay_minutes' => 1,
        ],
        'tracking' => [
            'attempts' => 500,
            'decay_minutes' => 1,
        ],
        'export' => [
            'attempts' => 5,
            'decay_minutes' => 60,
        ],
        'password_reset' => [
            'attempts' => 3,
            'decay_minutes' => 60,
        ],
        'registration' => [
            'attempts' => 3,
            'decay_minutes' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist
    |--------------------------------------------------------------------------
    */

    'whitelist' => [
        'ips' => explode(',', env('THROTTLE_WHITELIST_IPS', '')),
        'bypass_limits' => env('THROTTLE_WHITELIST_BYPASS', false),
    ],

];
