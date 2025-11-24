# SingaPay PHP SDK

Official PHP SDK for SingaPay Payment Gateway

## Overview

The SingaPay PHP SDK provides a seamless integration with SingaPay's payment gateway services. This SDK supports various payment methods and features including virtual accounts, payment links, disbursements, and more.

## Requirements

- PHP 7.4 or higher
- cURL extension
- JSON extension
- GuzzleHTTP 7.0+

## Installation

### Using Composer

```bash
composer require singapay/payment-gateway
```

### Manual Installation

Download the SDK and include the autoloader:

```php
require_once '/path/to/sdk/vendor/autoload.php';
```

## Configuration

### Basic Configuration

```php
use SingaPay\SingaPay;

$singapay = new SingaPay([
    'client_id' => 'your-client-id',
    'client_secret' => 'your-client-secret',
    'api_key' => 'your-api-key',
    'hmac_validation_key' => 'your-hmac-key',
    'environment' => 'sandbox', // or 'production'
]);
```

### Enhanced Configuration

```php
$singapay = new SingaPay([
    'client_id' => 'your-client-id',
    'client_secret' => 'your-client-secret',
    'api_key' => 'your-api-key',
    'hmac_validation_key' => 'your-hmac-key',
    'environment' => 'sandbox',

    // Enhanced features
    'timeout' => 60,
    'max_retries' => 5,
    'retry_delay' => 2000,
    'auto_reauth' => true,
    'cache_ttl' => 1800,
    'custom_headers' => [
        'X-Custom-Header' => 'CustomValue'
    ]
]);
```

### Factory Pattern for Multiple Configurations

```php
use SingaPay\SingaPayFactory;

// Set default configuration
SingaPayFactory::setDefaultConfig([
    'client_id' => 'default-client-id',
    'client_secret' => 'default-client-secret',
    'api_key' => 'default-api-key',
    'environment' => 'sandbox',
]);

// Create multiple instances
$clientA = SingaPayFactory::create($configA, 'client_a');
$clientB = SingaPayFactory::create($configB, 'client_b');

// Get instances by name
$clientA = SingaPayFactory::get('client_a');
```

## Available Features

### Account Management

- `list()` - Retrieve list of accounts
- `get($accountId)` - Get account details
- `create(array $data)` - Create new account
- `updateStatus($accountId, $status)` - Update account status
- `delete($accountId)` - Delete account

### Payment Links

- `list($accountId)` - Retrieve payment links
- `get($accountId, $paymentLinkId)` - Get payment link details
- `create($accountId, array $data)` - Create payment link
- `update($accountId, $paymentLinkId, array $data)` - Update payment link
- `delete($accountId, $paymentLinkId)` - Delete payment link
- `getAvailablePaymentMethods()` - Get available payment methods

### Virtual Accounts

- `list($accountId)` - Retrieve virtual accounts
- `get($accountId, $vaId)` - Get virtual account details
- `create($accountId, array $data)` - Create virtual account
- `update($accountId, $vaId, array $data)` - Update virtual account
- `delete($accountId, $vaId)` - Delete virtual account

### Disbursement

- `list($accountId)` - Retrieve disbursement history
- `get($accountId, $transactionId)` - Get disbursement details
- `checkFee($accountId, $amount, $bankSwiftCode)` - Check transfer fee
- `checkBeneficiary($bankAccountNumber, $bankSwiftCode)` - Check beneficiary account
- `transfer($accountId, array $data)` - Transfer funds

## Advanced Features

### Retry Mechanism

- Automatic retry on failed requests
- Configurable retry attempts and delay
- Exponential backoff strategy

### Authentication & Caching

- Automatic token management
- Token caching with TTL
- Auto-refresh on token expiration

### Error Handling

- Comprehensive exception hierarchy
- Validation error details
- HTTP status code mapping

### Monitoring & Metrics

- Request/response logging
- Performance metrics
- Success/failure statistics

### Webhook Verification

- HMAC signature verification
- Timestamp validation
- Payload integrity check

## Utility Methods

- `testConnection()` - Test API connectivity
- `getMetrics()` - Get request metrics
- `verifyWebhookSignature()` - Verify webhook authenticity
- `flushAuthCache()` - Clear authentication cache
- `addInterceptor()` - Add request/response interceptor

## Error Handling

The SDK throws specific exceptions for different error scenarios:

- `SingaPayException` - Base exception class
- `AuthenticationException` - Authentication failures
- `ValidationException` - Request validation errors
- `ApiException` - API communication errors

## Support

For detailed API documentation and additional examples, please refer to the official SingaPay documentation.

## License

This SDK is released under the MIT License.
