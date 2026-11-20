<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Active football data provider
    |--------------------------------------------------------------------------
    | "api_football" — API-Football v3 (api-sports.io). Requires a licensed key.
    | "demo"         — clearly-labelled fictional demonstration data for development.
    |
    | When "api_football" is selected but no key is configured (env or admin
    | settings), the platform falls back to "demo" only if demo_fallback is true.
    */
    'provider' => env('SPORTS_DATA_PROVIDER', 'api_football'),

    'demo_fallback' => filled(env('SPORTS_DATA_DEMO_FALLBACK'))
        ? filter_var(env('SPORTS_DATA_DEMO_FALLBACK'), FILTER_VALIDATE_BOOL)
        : env('APP_ENV') !== 'production',

    'api_football' => [
        'base_url' => env('API_FOOTBALL_BASE_URL', 'https://v3.football.api-sports.io'),
        'key' => env('API_FOOTBALL_KEY'),
        'timeout' => (int) env('API_FOOTBALL_TIMEOUT', 15),
        // Requests per minute permitted by your plan (Free: 10, Pro: 300, Ultra: 450, Mega: 900).
        'requests_per_minute' => (int) env('API_FOOTBALL_RPM', 10),
        // Stop making calls when the provider's remaining daily quota falls to this value.
        'daily_quota_reserve' => (int) env('API_FOOTBALL_QUOTA_RESERVE', 5),
    ],

    // Response cache TTLs (seconds).
    'cache' => [
        'live' => 20,
        'fixtures' => 600,
        'standings' => 1800,
        'head_to_head' => 43200,
    ],

    // After this many consecutive failures the provider is paused for `circuit_cooldown` seconds.
    'circuit_failures' => 5,
    'circuit_cooldown' => 300,

    // How many days ahead/behind the scheduled date sync covers.
    'sync_days_ahead' => (int) env('SPORTS_SYNC_DAYS_AHEAD', 3),
    'sync_days_behind' => (int) env('SPORTS_SYNC_DAYS_BEHIND', 1),
];
