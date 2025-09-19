<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tracking Configuration
    |--------------------------------------------------------------------------
    */

    'link_prefix' => env('TRACKING_LINK_PREFIX', 'r'),
    'token_length' => env('TRACKING_TOKEN_LENGTH', 8),
    'cookie_duration' => env('TRACKING_COOKIE_DURATION', 30), // days
    'session_timeout' => env('TRACKING_SESSION_TIMEOUT', 30), // minutes

    /*
    |--------------------------------------------------------------------------
    | Attribution Configuration
    |--------------------------------------------------------------------------
    */

    'attribution' => [
        'sources' => [
            'meta' => [
                'name' => 'Meta Ads',
                'patterns' => ['facebook.com', 'instagram.com', 'fb.com'],
                'utm_sources' => ['facebook', 'instagram', 'meta'],
                'url_params' => ['fbclid'],
            ],
            'google' => [
                'name' => 'Google Ads',
                'patterns' => ['google.com', 'googleads.com'],
                'utm_sources' => ['google', 'googleads'],
                'url_params' => ['gclid'],
            ],
            'outras' => [
                'name' => 'Outras Origens',
                'patterns' => [], // Will catch other UTM sources
                'utm_sources' => [],
                'url_params' => [],
            ],
        ],
        'default_source' => 'nao_rastreada',
        'utm_params' => [
            'utm_source',
            'utm_medium', 
            'utm_campaign',
            'utm_term',
            'utm_content',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | JavaScript Snippet Configuration
    |--------------------------------------------------------------------------
    */

    'snippet' => [
        'cookie_name' => 'visionmetrics_tracking',
        'local_storage_key' => 'vm_attribution',
        'api_endpoint' => '/api/tracking/capture',
        'auto_capture' => true,
        'capture_scroll' => false,
        'capture_clicks' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Meta Conversions API
    |--------------------------------------------------------------------------
    */

    'meta_conversions' => [
        'pixel_id' => env('META_PIXEL_ID'),
        'access_token' => env('META_ACCESS_TOKEN'),
        'test_event_code' => env('META_TEST_EVENT_CODE'),
        'api_version' => 'v18.0',
        'base_url' => 'https://graph.facebook.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Ads Configuration
    |--------------------------------------------------------------------------
    */

    'google_ads' => [
        'customer_id' => env('GOOGLE_ADS_CUSTOMER_ID'),
        'conversion_action_id' => env('GOOGLE_ADS_CONVERSION_ACTION_ID'),
        'developer_token' => env('GOOGLE_ADS_DEVELOPER_TOKEN'),
        'client_id' => env('GOOGLE_ADS_CLIENT_ID'),
        'client_secret' => env('GOOGLE_ADS_CLIENT_SECRET'),
        'refresh_token' => env('GOOGLE_ADS_REFRESH_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    */

    'webhooks' => [
        'timeout' => 30, // seconds
        'retry_attempts' => 3,
        'retry_delay' => 5, // seconds
        'max_payload_size' => 1024 * 1024, // 1MB
        'events' => [
            'lead.created',
            'conversation.started',
            'conversion.detected',
            'conversion.confirmed',
            'trial.expired',
        ],
    ],

];
