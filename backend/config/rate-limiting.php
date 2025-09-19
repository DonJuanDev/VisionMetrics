<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | By default, Laravel includes throttling middleware that can be used to
    | rate limit requests. You may configure the rate limiting options here
    | to control how many requests can be made within a given time period.
    |
    */

    'throttle' => [
        'api' => [
            'max_attempts' => env('THROTTLE_API_REQUESTS', 100),
            'decay_minutes' => 1,
        ],
        'login' => [
            'max_attempts' => env('THROTTLE_LOGIN_ATTEMPTS', 5),
            'decay_minutes' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Rate Limits
    |--------------------------------------------------------------------------
    |
    | Here you may define custom rate limiting configurations for specific
    | routes or groups of routes. These can be used to apply different
    | rate limiting rules to different parts of your application.
    |
    */

    'custom' => [
        'webhook' => [
            'max_attempts' => 1000,
            'decay_minutes' => 1,
        ],
        'tracking' => [
            'max_attempts' => 500,
            'decay_minutes' => 1,
        ],
        'export' => [
            'max_attempts' => 5,
            'decay_minutes' => 60,
        ],
        'password_reset' => [
            'max_attempts' => 3,
            'decay_minutes' => 60,
        ],
        'registration' => [
            'max_attempts' => 3,
            'decay_minutes' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist
    |--------------------------------------------------------------------------
    |
    | Here you may specify IP addresses that should be exempt from rate
    | limiting. These IPs will not be subject to any rate limiting rules.
    |
    */

    'whitelist' => [
        'ips' => explode(',', env('THROTTLE_WHITELIST_IPS', '')),
        'bypass_limits' => env('THROTTLE_WHITELIST_BYPASS', false),
    ],

];

