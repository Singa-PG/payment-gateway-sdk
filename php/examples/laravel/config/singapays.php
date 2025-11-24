<?php

return [
    'default' => env('SINGAPAY_ENVIRONMENT', 'sandbox'),

    'environments' => [
        'sandbox' => [
            'client_id' => env('SINGAPAY_CLIENT_ID'),
            'client_secret' => env('SINGAPAY_CLIENT_SECRET'),
            'api_key' => env('SINGAPAY_API_KEY'),
            'hmac_validation_key' => env('SINGAPAY_HMAC_KEY'),
            'environment' => 'sandbox',
        ],
        'production' => [
            'client_id' => env('SINGAPAY_CLIENT_ID'),
            'client_secret' => env('SINGAPAY_CLIENT_SECRET'),
            'api_key' => env('SINGAPAY_API_KEY'),
            'hmac_validation_key' => env('SINGAPAY_HMAC_KEY'),
            'environment' => 'production',
        ],
    ],

    // Enhanced configuration
    'timeout' => 60,
    'max_retries' => 3,
    'retry_delay' => 2000,
    'auto_reauth' => true,
];
