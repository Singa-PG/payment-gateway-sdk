# SingaPay PHP SDK

<!-- [![Latest Version](https://img.shields.io/packagist/v/singapay/payment-gateway.svg)](https://packagist.org/packages/singapay/payment-gateway)
[![License](https://img.shields.io/packagist/l/singapay/payment-gateway.svg)](https://packagist.org/packages/singapay/payment-gateway)
[![PHP Version](https://img.shields.io/packagist/php-v/singapay/payment-gateway.svg)](https://packagist.org/packages/singapay/payment-gateway) -->

Official PHP SDK for seamless integration with SingaPay Payment Gateway. This SDK provides a comprehensive, easy-to-use interface for accessing SingaPay's payment services including virtual accounts, payment links, QRIS, disbursements, and more.

## What is SingaPay SDK?

SingaPay PHP SDK is a powerful library that simplifies the integration of SingaPay payment services into your PHP applications. It handles authentication, request signing, error handling, and provides an intuitive API for all SingaPay features.

### Key Benefits

- **Easy Integration**: Simple, fluent API design for quick implementation
- **Secure by Default**: Built-in authentication, signature generation, and webhook verification
- **Production Ready**: Automatic retry logic, token caching, and comprehensive error handling
- **Framework Support**: Native support for Laravel, CodeIgniter, Symfony, and vanilla PHP
- **Type Safe**: Full type hints and validation for better IDE support

## Requirements

- PHP 7.4 or higher
- cURL extension enabled
- JSON extension enabled
- Composer (recommended)

## Quick Installation

### Using Composer (Recommended)

```bash
composer require singapay/payment-gateway
```

### Manual Installation

1. Download the SDK from [GitHub releases](https://github.com/singapay/php-sdk/releases)
2. Extract the archive
3. Include the autoloader in your project:

```php
require_once '/path/to/singapay-sdk/vendor/autoload.php';
```

## Framework-Specific Installation

### Laravel

Install via Composer:

```bash
composer require singapay/payment-gateway
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=singapay-config
```

Configure in `config/singapay.php` or `.env`:

```env
SINGAPAY_CLIENT_ID=your-client-id
SINGAPAY_CLIENT_SECRET=your-client-secret
SINGAPAY_API_KEY=your-api-key
SINGAPAY_HMAC_KEY=your-hmac-key
SINGAPAY_ENVIRONMENT=sandbox
```

Use in your code:

```php
use SingaPay\SingaPay;

class PaymentController extends Controller
{
    private $singapay;

    public function __construct(SingaPay $singapay)
    {
        $this->singapay = $singapay;
    }

    public function createPayment()
    {
        $account = $this->singapay->account->get('your-account-id');
        return response()->json($account);
    }
}
```

### CodeIgniter 4

Install via Composer:

```bash
composer require singapay/payment-gateway
```

Create a service in `app/Config/Services.php`:

```php
use SingaPay\SingaPay;

public static function singapay($getShared = true)
{
    if ($getShared) {
        return static::getSharedInstance('singapay');
    }

    return new SingaPay([
        'client_id' => getenv('SINGAPAY_CLIENT_ID'),
        'client_secret' => getenv('SINGAPAY_CLIENT_SECRET'),
        'api_key' => getenv('SINGAPAY_API_KEY'),
        'hmac_validation_key' => getenv('SINGAPAY_HMAC_KEY'),
        'environment' => getenv('SINGAPAY_ENVIRONMENT'),
    ]);
}
```

Use in your controller:

```php
namespace App\Controllers;

class Payment extends BaseController
{
    public function index()
    {
        $singapay = service('singapay');
        $accounts = $singapay->account->list();

        return $this->response->setJSON($accounts);
    }
}
```

### Symfony

Install via Composer:

```bash
composer require singapay/payment-gateway
```

Register as a service in `config/services.yaml`:

```yaml
services:
  SingaPay\SingaPay:
    arguments:
      $config:
        client_id: "%env(SINGAPAY_CLIENT_ID)%"
        client_secret: "%env(SINGAPAY_CLIENT_SECRET)%"
        api_key: "%env(SINGAPAY_API_KEY)%"
        hmac_validation_key: "%env(SINGAPAY_HMAC_KEY)%"
        environment: "%env(SINGAPAY_ENVIRONMENT)%"
```

Use in your controller:

```php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use SingaPay\SingaPay;

class PaymentController extends AbstractController
{
    public function index(SingaPay $singapay)
    {
        $accounts = $singapay->account->list();
        return $this->json($accounts);
    }
}
```

### Vanilla PHP

```php
<?php
require_once 'vendor/autoload.php';

use SingaPay\SingaPay;

$singapay = new SingaPay([
    'client_id' => 'your-client-id',
    'client_secret' => 'your-client-secret',
    'api_key' => 'your-api-key',
    'hmac_validation_key' => 'your-hmac-key',
    'environment' => 'sandbox', // or 'production'
]);

// Use the SDK
$accounts = $singapay->account->list();
print_r($accounts);
```

### WordPress

Add to your theme's `functions.php` or create a plugin:

```php
require_once get_template_directory() . '/vendor/autoload.php';

use SingaPay\SingaPay;

function get_singapay_instance() {
    static $singapay = null;

    if ($singapay === null) {
        $singapay = new SingaPay([
            'client_id' => get_option('singapay_client_id'),
            'client_secret' => get_option('singapay_client_secret'),
            'api_key' => get_option('singapay_api_key'),
            'hmac_validation_key' => get_option('singapay_hmac_key'),
            'environment' => get_option('singapay_environment', 'sandbox'),
        ]);
    }

    return $singapay;
}

// Use in your code
add_action('init', function() {
    $singapay = get_singapay_instance();
    // Your payment logic here
});
```

## Quick Start Example

```php
<?php
require_once 'vendor/autoload.php';

use SingaPay\SingaPay;

// Initialize SDK
$singapay = new SingaPay([
    'client_id' => 'your-client-id',
    'client_secret' => 'your-client-secret',
    'api_key' => 'your-api-key',
    'hmac_validation_key' => 'your-hmac-key',
    'environment' => 'sandbox',
]);

try {
    // Create a virtual account
    $va = $singapay->virtualAccount->create('account-id', [
        'bank_code' => 'BCA',
        'amount' => 100000,
        'kind' => 'temporary',
        'expired_at' => '2024-12-31 23:59:59',
        'reference_number' => 'ORDER-12345'
    ]);

    echo "Virtual Account Created: " . $va['va_number'];

    // Create a payment link
    $paymentLink = $singapay->paymentLink->create('account-id', [
        'reff_no' => 'PL-12345',
        'title' => 'Product Purchase',
        'total_amount' => 100000,
        'items' => [
            [
                'name' => 'Product A',
                'quantity' => 1,
                'unit_price' => 100000
            ]
        ]
    ]);

    echo "Payment Link: " . $paymentLink['payment_url'];

} catch (\SingaPay\Exceptions\ValidationException $e) {
    echo "Validation Error: " . $e->getMessage();
    print_r($e->getErrors());
} catch (\SingaPay\Exceptions\ApiException $e) {
    echo "API Error: " . $e->getMessage();
}
```

## Available Features

The SDK provides access to all SingaPay services:

- **Account Management** - Create and manage accounts
- **Virtual Accounts** - Generate and manage virtual account numbers
- **Payment Links** - Create shareable payment links
- **QRIS** - Generate dynamic QRIS codes
- **Disbursement** - Transfer funds to bank accounts
- **Cardless Withdrawal** - ATM withdrawals without cards
- **Balance Inquiry** - Check account balances
- **Statements** - Retrieve transaction statements
- **Payment History** - Track payment transactions

## Documentation

For complete documentation, please visit:

- **[Full Documentation](./docs/DOCUMENTATION.md)** - Complete API reference
- **[API Reference](./docs/API_REFERENCE.md)** - Detailed method documentation
- **[Examples](./docs/EXAMPLES.md)** - Practical code examples
- **[Advanced Usage](./docs/ADVANCED.md)** - Advanced features and configurations
- **[Webhook Guide](./docs/WEBHOOKS.md)** - Webhook integration guide

## Support & Resources

- **Official Documentation**: [https://docs.singapay.id](https://docs.singapay.id)
- **API Documentation**: [https://api-docs.singapay.id](https://api-docs.singapay.id)
- **Developer Portal**: [https://developer.singapay.id](https://developer.singapay.id)
- **GitHub Issues**: [https://github.com/singapay/php-sdk/issues](https://github.com/singapay/php-sdk/issues)
- **Email Support**: developer@singapay.id

## Testing

```bash
# Run tests
composer test

# Run tests with coverage
composer test:coverage
```

## Security

If you discover any security vulnerabilities, please email security@singapay.id instead of using the issue tracker.

## Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md) for details.

## License

This SDK is open-sourced software licensed under the [MIT license](LICENSE).

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

---

Made by **SingaPay**
