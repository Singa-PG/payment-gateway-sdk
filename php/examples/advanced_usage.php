<?php

require_once __DIR__ . '/../vendor/autoload.php';

use SingaPay\SingaPay;
use SingaPay\SingaPayFactory;
use SingaPay\Cache\ArrayCache;

// Method 1: Basic usage with enhanced features
$singapay = new SingaPay([
    'client_id' => '',
    'client_secret' => '',
    'api_key' => '',
    'hmac_validation_key' => '',
    'environment' => 'sandbox',

    // Enhanced configuration
    'timeout' => 60,
    'max_retries' => 5,
    'retry_delay' => 2000,
    'auto_reauth' => true,
    'cache_ttl' => 1800,
    'custom_headers' => [
        'X-Custom-Header' => 'CustomValue'
    ]
]);

// Test connection
$connectionTest = $singapay->testConnection();
echo "Connection test: " . ($connectionTest['success'] ? 'SUCCESS' : 'FAILED') . "\n";

// Method 2: Factory pattern for multiple configurations
SingaPayFactory::setDefaultConfig([
    'client_id' => 'default-client-id',
    'client_secret' => 'default-client-secret',
    'api_key' => 'default-api-key',
    'environment' => 'sandbox',
]);

// Create multiple instances
$clientA = SingaPayFactory::create([
    'client_id' => 'client-a-id',
    'client_secret' => 'client-a-secret',
    'api_key' => 'client-a-key',
    'environment' => 'sandbox',
], 'client_a');

$clientB = SingaPayFactory::create([
    'client_id' => 'client-b-id',
    'client_secret' => 'client-b-secret',
    'api_key' => 'client-b-key',
    'environment' => 'production',
], 'client_b');

// Get instances by name
$clientA = SingaPayFactory::get('client_a');
$clientB = SingaPayFactory::get('client_b');

echo "Available instances: " . implode(', ', SingaPayFactory::getInstanceNames()) . "\n";

// Example usage with auto-retry and reauth
try {
    // This will automatically retry on failure and re-authenticate if needed
    $accounts = $clientA->account->list();
    echo "Found " . count($accounts) . " accounts\n";

    // Get metrics
    $metrics = $clientA->getMetrics();
    echo "Total requests: " . $metrics['total_requests'] . "\n";
    echo "Successful requests: " . $metrics['successful_requests'] . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
