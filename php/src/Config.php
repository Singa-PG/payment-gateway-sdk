<?php

namespace SingaPay;

/**
 * Class Config
 *
 * Represents the configuration container for the SingaPay Payment Gateway SDK.
 * This class stores all required credentials, environment settings, retry policies,
 * caching rules, and custom HTTP headers.
 *
 * The Config object is required by all SDK services and clients to authenticate,
 * communicate with the API, and control runtime behavior.
 *
 * @package SingaPay
 */
class Config
{
    /** @var string|null Client ID provided by SingaPay */
    private $clientId;

    /** @var string|null Client Secret provided by SingaPay */
    private $clientSecret;

    /** @var string|null API Key for merchant authentication */
    private $apiKey;

    /** @var string|null Optional HMAC validation key for webhook signature verification */
    private $hmacValidationKey;

    /** @var string Current environment ("sandbox" or "production") */
    private $environment;

    /** @var string Base API URL depending on selected environment */
    private $baseUrl;

    /** @var int HTTP request timeout in seconds */
    private $timeout;

    /** @var int Maximum number of retry attempts for failed requests */
    private $maxRetries;

    /** @var int Delay before retrying a failed request (in milliseconds) */
    private $retryDelay;

    /** @var bool Whether the SDK should automatically re-authenticate on token expiry */
    private $autoReauth;

    /** @var int Cache lifetime for stored authorization tokens (seconds) */
    private $cacheTtl;

    /** @var array Custom HTTP headers to send with every request */
    private $customHeaders;

    /**
     * Config constructor.
     *
     * Accepts an array of configuration options and initializes all required
     * SDK settings. Missing optional values will fall back to default constants.
     *
     * @param array $config Associative array with configuration fields:
     *
     * Required:
     * - client_id (string)
     * - client_secret (string)
     * - api_key (string)
     *
     * Optional:
     * - hmac_validation_key (string)
     * - environment (string) "sandbox" or "production"
     * - base_url (string) Custom endpoint override
     * - timeout (int)
     * - max_retries (int)
     * - retry_delay (int) in milliseconds
     * - auto_reauth (bool)
     * - cache_ttl (int)
     * - custom_headers (array)
     *
     * @throws \InvalidArgumentException When required fields are missing or invalid
     */
    public function __construct(array $config = [])
    {
        $this->clientId           = $config['client_id'] ?? null;
        $this->clientSecret       = $config['client_secret'] ?? null;
        $this->apiKey             = $config['api_key'] ?? null;
        $this->hmacValidationKey  = $config['hmac_validation_key'] ?? null;

        $this->environment = $config['environment'] ?? Constant::ENV_SANDBOX;
        $this->baseUrl     = $config['base_url'] ?? $this->getDefaultBaseUrl();

        $this->timeout     = $config['timeout']      ?? Constant::DEFAULT_TIMEOUT;
        $this->maxRetries  = $config['max_retries']  ?? Constant::DEFAULT_MAX_RETRIES;
        $this->retryDelay  = $config['retry_delay']  ?? Constant::DEFAULT_RETRY_DELAY;
        $this->autoReauth  = $config['auto_reauth']  ?? Constant::DEFAULT_AUTO_REAUTH;
        $this->cacheTtl    = $config['cache_ttl']    ?? Constant::DEFAULT_CACHE_TTL;

        $this->customHeaders = $config['custom_headers'] ?? [];

        $this->validate();
    }

    /**
     * Validate configuration values.
     *
     * Ensures that required fields are present and contain valid data.
     *
     * @throws \InvalidArgumentException If any required value is invalid
     */
    private function validate()
    {
        $required = ['clientId', 'clientSecret', 'apiKey'];

        foreach ($required as $field) {
            if (empty($this->$field)) {
                throw new \InvalidArgumentException("Config field '{$field}' is required");
            }
        }

        if (!in_array($this->environment, [
            Constant::ENV_SANDBOX,
            Constant::ENV_PRODUCTION
        ])) {
            throw new \InvalidArgumentException("Invalid environment. Must be 'sandbox' or 'production'");
        }

        if ($this->maxRetries < 0) {
            throw new \InvalidArgumentException("Max retries must be non-negative");
        }
    }

    /**
     * Resolve the default base URL depending on environment.
     *
     * @return string
     */
    private function getDefaultBaseUrl()
    {
        return $this->environment === Constant::ENV_PRODUCTION
            ? Constant::PRODUCTION_URL
            : Constant::SANDBOX_URL;
    }

    /** @return string|null */
    public function getClientId()
    {
        return $this->clientId;
    }

    /** @return string|null */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /** @return string|null */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /** @return string|null */
    public function getHmacValidationKey()
    {
        return $this->hmacValidationKey;
    }

    /** @return string */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /** @return string */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /** @return int */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /** @return int */
    public function getMaxRetries()
    {
        return $this->maxRetries;
    }

    /** @return int */
    public function getRetryDelay()
    {
        return $this->retryDelay;
    }

    /** @return bool */
    public function isAutoReauthEnabled()
    {
        return $this->autoReauth;
    }

    /** @return int */
    public function getCacheTtl()
    {
        return $this->cacheTtl;
    }

    /** @return array */
    public function getCustomHeaders()
    {
        return $this->customHeaders;
    }

    /** @return bool */
    public function isSandbox()
    {
        return $this->environment === Constant::ENV_SANDBOX;
    }

    /** @return bool */
    public function isProduction()
    {
        return $this->environment === Constant::ENV_PRODUCTION;
    }

    /**
     * Set the HTTP timeout value.
     *
     * @param int $timeout Seconds
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Set the maximum retry attempts.
     *
     * @param int $maxRetries
     * @return $this
     */
    public function setMaxRetries($maxRetries)
    {
        $this->maxRetries = $maxRetries;
        return $this;
    }

    /**
     * Set the delay before retrying failed requests.
     *
     * @param int $retryDelay Milliseconds
     * @return $this
     */
    public function setRetryDelay($retryDelay)
    {
        $this->retryDelay = $retryDelay;
        return $this;
    }

    /**
     * Enable or disable automatic token re-authentication.
     *
     * @param bool $enabled
     * @return $this
     */
    public function setAutoReauth($enabled)
    {
        $this->autoReauth = $enabled;
        return $this;
    }

    /**
     * Set the cached token lifetime.
     *
     * @param int $ttl Seconds
     * @return $this
     */
    public function setCacheTtl($ttl)
    {
        $this->cacheTtl = $ttl;
        return $this;
    }

    /**
     * Add or override a custom HTTP header.
     *
     * @param string $name Header name
     * @param string $value Header value
     * @return $this
     */
    public function addCustomHeader($name, $value)
    {
        $this->customHeaders[$name] = $value;
        return $this;
    }

    /**
     * Remove a custom HTTP header.
     *
     * @param string $name Header name
     * @return $this
     */
    public function removeCustomHeader($name)
    {
        unset($this->customHeaders[$name]);
        return $this;
    }

    /**
     * Convert config to array
     * 
     * @return array
     */
    public function toArray()
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => '***HIDDEN***',
            'api_key' => substr($this->apiKey, 0, 8) . '...',
            'environment' => $this->environment,
            'base_url' => $this->baseUrl,
            'timeout' => $this->timeout,
            'max_retries' => $this->maxRetries,
            'retry_delay' => $this->retryDelay,
            'auto_reauth' => $this->autoReauth,
            'cache_ttl' => $this->cacheTtl,
        ];
    }
}
