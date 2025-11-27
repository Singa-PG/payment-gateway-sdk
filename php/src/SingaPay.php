<?php

namespace SingaPay;

use SingaPay\Http\Client;
use SingaPay\Security\Authentication;
use SingaPay\Resources\Account;
use SingaPay\Resources\PaymentLink;
use SingaPay\Resources\VirtualAccount;
use SingaPay\Resources\Disbursement;
use SingaPay\Resources\PaymentLinkHistory;
use SingaPay\Resources\VATransaction;
use SingaPay\Resources\BalanceInquiry;
use SingaPay\Resources\Statement;
use SingaPay\Resources\Qris;
use SingaPay\Resources\CardlessWithdrawal;
use SingaPay\Cache\ArrayCache;

/**
 * SingaPay PHP SDK
 * 
 * Official PHP SDK for integrating with SingaPay Payment Gateway services.
 * This SDK provides comprehensive access to SingaPay's payment processing,
 * virtual accounts, payment links, and disbursement features, etc.
 * 
 * @package SingaPay
 * @author PT. Abadi Singapay Indonesia
  */
class SingaPay
{
    /**
     * @var string SDK version
     */
    private static $version;

    /**
     * @var Config Configuration instance
     */
    private $config;

    /**
     * @var Client HTTP client instance
     */
    private $client;

    /**
     * @var Authentication Authentication handler instance
     */
    private $auth;

    /**
     * @var Account Account management resource
     */
    public $account;

    /**
     * @var PaymentLink Payment link features resource
     */
    public $paymentLink;

    /**
     * @var VirtualAccount Virtual account features resource
     */
    public $virtualAccount;

    /**
     * @var Disbursement Disbursement and transfer features resource
     */
    public $disbursement;

    /**
     * @var PaymentLinkHistory Payment link history features resource
     */
    public $paymentLinkHistory;

    /**
     * @var VATransaction Virtual account transaction features resource
     */
    public $vaTransaction;

    /**
     * @var BalanceInquiry Balance inquiry features resource
     */
    public $balanceInquiry;

    /**
     * @var Statement Statement features resource
     */
    public $statement;

    /**
     * @var Qris QRIS features resource
     */
    public $qris;

    /**
     * @var CardlessWithdrawal Cardless withdrawal features resource
     */
    public $cardlessWithdrawal;

    /**
     * Initialize SingaPay SDK with provided configuration
     * 
     * Constructs a new SingaPay instance with the specified configuration options.
     * Validates configuration, initializes dependencies, and sets up resource instances.
     * 
     * @param array $config Configuration array containing authentication and SDK settings
     * 
     * Required configuration parameters:
     * - client_id: Your SingaPay client identifier
     * - client_secret: Your SingaPay client secret for authentication
     * - api_key: Your SingaPay API key for request authorization
     * - hmac_validation_key: Key for webhook signature verification
     * - environment: Operating environment ('sandbox' or 'production')
     * 
     * Optional configuration parameters:
     * - timeout: HTTP request timeout in seconds (default: 30)
     * - max_retries: Maximum number of retry attempts for failed requests (default: 3)
     * - retry_delay: Delay between retry attempts in milliseconds (default: 1000)
     * - auto_reauth: Enable automatic re-authentication on token expiry (default: true)
     * - cache_ttl: Token cache time-to-live in seconds (default: 3600)
     * - custom_headers: Additional HTTP headers to include in requests
     * 
     * @throws \InvalidArgumentException When required configuration parameters are missing or invalid
     * @throws \RuntimeException When SDK initialization fails
     * 
     * @example
     * $singapay = new SingaPay([
     *     'client_id' => 'your-client-id',
     *     'client_secret' => 'your-client-secret',
     *     'api_key' => 'your-api-key',
     *     'hmac_validation_key' => 'your-hmac-key',
     *     'environment' => 'sandbox'
     * ]);
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);
        $this->initializeDependencies();
        $this->initializeResources();
    }

    /**
     * Initialize SDK dependencies with dependency injection support
     * 
     * Sets up the HTTP client and authentication handler. Supports dependency injection
     * for testing or custom implementations. When no custom instances are provided,
     * creates default instances with ArrayCache for token storage.
     * 
     * @param Client|null $client Custom HTTP client instance (optional)
     * @param Authentication|null $auth Custom authentication handler (optional)
     * 
     * @return void
     */
    private function initializeDependencies(?Client $client = null, ?Authentication $auth = null)
    {
        if ($client === null) {
            $this->auth = $auth ?? new Authentication(
                $this->config,
                null,
                new ArrayCache()
            );

            $this->client = new Client($this->config, $this->auth);

            $this->auth->setClient($this->client);
        } else {
            $this->client = $client;
            $this->auth = $auth;
        }
    }

    /**
     * Initialize resource instances for API operations
     * 
     * Creates instances of all available API resources with proper dependency injection.
     * Each resource is accessible as a public property of the main SingaPay instance.
     * 
     * @return void
     */
    private function initializeResources()
    {
        $apiKey = $this->config->getApiKey();

        $this->account = new Account($this->client, $this->auth, $apiKey);
        $this->paymentLink = new PaymentLink($this->client, $this->auth, $apiKey);
        $this->virtualAccount = new VirtualAccount($this->client, $this->auth, $apiKey);
        $this->disbursement = new Disbursement($this->client, $this->auth, $this->config);

        $this->paymentLinkHistory = new PaymentLinkHistory($this->client, $this->auth, $apiKey);
        $this->vaTransaction = new VATransaction($this->client, $this->auth, $apiKey);

        $this->balanceInquiry = new BalanceInquiry($this->client, $this->auth, $apiKey);
        $this->statement = new Statement($this->client, $this->auth, $apiKey);

        $this->qris = new Qris($this->client, $this->auth, $apiKey);
        $this->cardlessWithdrawal = new CardlessWithdrawal($this->client, $this->auth, $apiKey);
    }

    /**
     * Get current SDK version
     * 
     * Retrieves the SDK version from composer.json file. If the composer.json file
     * is not accessible or doesn't contain version information.
     * The version is cached for subsequent calls to avoid repeated file operations.
     * 
     * @return string Current SDK version
     * 
     * @example
     * $version = SingaPay::getVersion();
     * echo "Using SingaPay SDK version: " . $version;
     */
    public static function getVersion()
    {
        if (self::$version === null) {
            $composerFile = __DIR__ . '/../../composer.json';
            if (file_exists($composerFile)) {
                $composerData = json_decode(file_get_contents($composerFile), true);
                self::$version = $composerData['version'] ?? '1.0.0';
            } else {
                self::$version = '1.0.0';
            }
        }

        return self::$version;
    }

    /**
     * Get configuration instance
     * 
     * Returns the Config instance containing all SDK configuration settings.
     * Useful for accessing configuration values or modifying settings dynamically.
     * 
     * @return Config Configuration instance
     * 
     * @example
     * $config = $singapay->getConfig();
     * $baseUrl = $config->getBaseUrl();
     * $environment = $config->getEnvironment();
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get HTTP client instance
     * 
     * Returns the HTTP client instance used for making API requests.
     * Useful for adding custom interceptors or accessing client metrics.
     * 
     * @return Client HTTP client instance
     * 
     * @example
     * $client = $singapay->getClient();
     * $metrics = $client->getMetrics();
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get authentication handler instance
     * 
     * Returns the authentication handler instance responsible for token management
     * and API authentication. Useful for manual token management or debugging.
     * 
     * @return Authentication Authentication handler instance
     * 
     * @example
     * $auth = $singapay->getAuth();
     * $token = $auth->getAccessToken();
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * Verify webhook signature authenticity
     * 
     * Validates incoming webhook requests by verifying the HMAC signature.
     * This ensures the webhook request originated from SingaPay and hasn't been tampered with.
     * 
     * @param string $timestamp Timestamp from webhook request headers
     * @param mixed $body Webhook request body (array or JSON string)
     * @param string $receivedSignature Signature from webhook request headers
     * 
     * @return bool True if signature is valid, false otherwise
     * 
     * @throws \InvalidArgumentException When HMAC validation key is not configured
     * 
     * @example
     * $isValid = $singapay->verifyWebhookSignature(
     *     $_SERVER['HTTP_X_TIMESTAMP'],
     *     file_get_contents('php://input'),
     *     $_SERVER['HTTP_X_SIGNATURE']
     * );
     * 
     * if ($isValid) {
     *     // Process webhook
     * } else {
     *     // Reject webhook
     * }
     */
    public function verifyWebhookSignature($timestamp, $body, $receivedSignature)
    {
        $hmacKey = $this->config->getHmacValidationKey();

        if (!$hmacKey) {
            throw new \InvalidArgumentException('HMAC Validation Key is required for webhook verification');
        }

        return \SingaPay\Security\Signature::verifyWebhook($timestamp, $body, $receivedSignature, $hmacKey);
    }

    /**
     * Add interceptor to HTTP client
     * 
     * Attaches a custom interceptor to monitor or modify HTTP requests and responses.
     * Interceptors can be used for logging, metrics collection, request transformation,
     * or custom error handling.
     * 
     * @param mixed $interceptor Interceptor instance implementing InterceptorInterface
     * 
     * @return self Returns SingaPay instance for method chaining
     * 
     * @example
     * $singapay->addInterceptor(new CustomLoggingInterceptor())
     *          ->addInterceptor(new MetricsCollectorInterceptor());
     */
    public function addInterceptor($interceptor)
    {
        $this->client->addInterceptor($interceptor);
        return $this;
    }

    /**
     * Get request metrics and statistics
     * 
     * Retrieves performance metrics and statistics collected by the HTTP client.
     * Includes total request count, success/failure rates, and response time data.
     * 
     * @return array Metrics data including:
     *               - total_requests: Total number of API requests made
     *               - successful_requests: Number of successful requests
     *               - failed_requests: Number of failed requests
     *               - total_response_time: Cumulative response time in seconds
     *               - last_request_time: Timestamp of last request
     * 
     * @example
     * $metrics = $singapay->getMetrics();
     * echo "Success rate: " . ($metrics['successful_requests'] / $metrics['total_requests'] * 100) . "%";
     */
    public function getMetrics()
    {
        return $this->client->getMetrics();
    }

    /**
     * Flush authentication cache and force token refresh
     * 
     * Clears cached authentication tokens and forces a fresh token acquisition
     * on the next API request. Useful when tokens need to be invalidated or
     * when switching between different user contexts.
     * 
     * @return self Returns SingaPay instance for method chaining
     * 
     * @example
     * // Force token refresh
     * $singapay->flushAuthCache();
     * 
     * // Subsequent calls will use new token
     * $accounts = $singapay->account->list();
     */
    public function flushAuthCache()
    {
        $this->auth->refreshToken();
        return $this;
    }

    /**
     * Test API connection and authentication
     * 
     * Verifies connectivity to SingaPay API and validates authentication credentials.
     * Attempts to obtain an access token using configured client credentials.
     * 
     * @return array Connection test result containing:
     *               - success: Boolean indicating connection success
     *               - message: Descriptive message about the result
     *               - token_obtained: Boolean indicating if access token was acquired
     *               - error_code: Error code if connection failed (only on failure)
     * 
     * @example
     * $connectionTest = $singapay->testConnection();
     * 
     * if ($connectionTest['success']) {
     *     echo "Connection successful. Token obtained: " . 
     *          ($connectionTest['token_obtained'] ? 'Yes' : 'No');
     * } else {
     *     echo "Connection failed: " . $connectionTest['message'];
     * }
     */
    public function testConnection()
    {
        try {
            $token = $this->auth->getAccessToken();
            return [
                'success' => true,
                'message' => 'Connection successful',
                'token_obtained' => !empty($token)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }
}
