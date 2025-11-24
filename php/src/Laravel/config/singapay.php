<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SingaPay Client ID
    |--------------------------------------------------------------------------
    |
    | Your SingaPay client identifier. This is provided when you register
    | your application with SingaPay and is required for API authentication.
    |
    */
    'client_id' => env('SINGAPAY_CLIENT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | SingaPay Client Secret
    |--------------------------------------------------------------------------
    |
    | Your SingaPay client secret for cryptographic authentication.
    | Keep this value secure and never expose it in client-side code.
    | Use environment variables for production deployments.
    |
    */
    'client_secret' => env('SINGAPAY_CLIENT_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | SingaPay API Key
    |--------------------------------------------------------------------------
    |
    | Your SingaPay API key for request authorization.
    | This key identifies your application to the SingaPay API.
    |
    */
    'api_key' => env('SINGAPAY_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | HMAC Validation Key
    |--------------------------------------------------------------------------
    |
    | Key for verifying webhook signatures from SingaPay.
    | This ensures webhook requests are authentic and haven't been tampered with.
    | Required for secure webhook processing.
    |
    */
    'hmac_validation_key' => env('SINGAPAY_HMAC_VALIDATION_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | SingaPay API environment. Use 'sandbox' for testing and development,
    | and 'production' for live transaction processing.
    |
    | Supported: "sandbox", "production"
    |
    */
    'environment' => env('SINGAPAY_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time in seconds to wait for API responses.
    | Adjust based on network conditions and expected API response times.
    |
    */
    'timeout' => env('SINGAPAY_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Maximum Retries
    |--------------------------------------------------------------------------
    |
    | Number of automatic retry attempts for failed API requests.
    | Retries are performed for temporary failures and server errors.
    |
    */
    'max_retries' => env('SINGAPAY_MAX_RETRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Retry Delay
    |--------------------------------------------------------------------------
    |
    | Initial delay between retry attempts in milliseconds.
    | The SDK implements exponential backoff for subsequent retries.
    |
    */
    'retry_delay' => env('SINGAPAY_RETRY_DELAY', 1000),

    /*
    |--------------------------------------------------------------------------
    | Auto Re-authentication
    |--------------------------------------------------------------------------
    |
    | Enable automatic token refresh when access tokens expire.
    | Recommended for production applications to ensure continuous operation.
    |
    */
    'auto_reauth' => env('SINGAPAY_AUTO_REAUTH', true),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    |
    | Time-to-live in seconds for cached authentication tokens.
    | Caching tokens reduces API calls and improves performance.
    |
    */
    'cache_ttl' => env('SINGAPAY_CACHE_TTL', 3600),
];
