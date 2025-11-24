<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    */

    'client_id' => env('SINGAPAY_CLIENT_ID'),
    'client_secret' => env('SINGAPAY_CLIENT_SECRET'),
    'api_key' => env('SINGAPAY_API_KEY'),
    'hmac_validation_key' => env('SINGAPAY_HMAC_VALIDATION_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    */

    'environment' => env('SINGAPAY_ENV', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Advanced Options
    |--------------------------------------------------------------------------
    */

    'timeout' => env('SINGAPAY_TIMEOUT', 30),
    'max_retries' => env('SINGAPAY_MAX_RETRIES', 3),
    'retry_delay' => env('SINGAPAY_RETRY_DELAY', 1000),
    'auto_reauth' => env('SINGAPAY_AUTO_REAUTH', true),
    'cache_ttl' => env('SINGAPAY_CACHE_TTL', 3600),
];
