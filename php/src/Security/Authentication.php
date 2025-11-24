<?php

namespace SingaPay\Security;

use SingaPay\Config;
use SingaPay\Http\Client;
use SingaPay\Exceptions\AuthenticationException;
use SingaPay\Cache\CacheInterface;
use SingaPay\Cache\ArrayCache;

/**
 * SingaPay Authentication Manager
 * 
 * Handles OAuth2 client credentials authentication and access token management
 * for SingaPay API integration. Provides automatic token caching, refresh,
 * and lifecycle management to ensure secure and efficient API access.
 * 
 * @package SingaPay\Security
 * @author PT. Abadi Singapay Indonesia
  */
class Authentication
{
    /**
     * Cache key prefix for token storage
     * 
     * @var string
     */
    const CACHE_KEY_PREFIX = 'singapay_token_';

    /**
     * @var Config SDK configuration instance
     */
    private $config;

    /**
     * @var Client HTTP client for API communication
     */
    private $client;

    /**
     * @var CacheInterface Token cache implementation
     */
    private $cache;

    /**
     * @var string Current access token
     */
    private $accessToken;

    /**
     * @var int Access token expiry timestamp
     */
    private $tokenExpiry;

    /**
     * Initialize Authentication Manager
     * 
     * Constructs a new authentication handler with provided configuration,
     * HTTP client, and cache implementation. Supports dependency injection
     * for testing and custom implementations.
     * 
     * @param Config $config SDK configuration containing client credentials
     * @param Client|null $client HTTP client instance (optional)
     * @param CacheInterface|null $cache Cache implementation (optional, defaults to ArrayCache)
     * 
     * @throws \InvalidArgumentException If configuration is invalid
     */
    public function __construct(Config $config, ?Client $client = null, ?CacheInterface $cache = null)
    {
        $this->config = $config;
        $this->client = $client;
        $this->cache = $cache ?? new ArrayCache();
    }

    /**
     * Set HTTP client instance
     * 
     * Allows setting or replacing the HTTP client instance after construction.
     * Useful for dependency injection and testing scenarios.
     * 
     * @param Client $client HTTP client instance for API communication
     * 
     * @return void
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get valid access token with automatic refresh
     * 
     * Retrieves a valid access token for API authentication. Implements
     * multi-layer caching strategy:
     * 1. Returns in-memory token if valid
     * 2. Returns cached token from storage if valid
     * 3. Requests new token from API if no valid token available
     * 
     * Tokens are automatically refreshed when expired or nearing expiry.
     * 
     * @return string Valid access token for API authentication
     * 
     * @throws AuthenticationException When token request fails or credentials are invalid
     * @throws \RuntimeException When HTTP client is not configured
     * 
     * @example
     * // Get token for API request
     * $token = $auth->getAccessToken();
     * 
     * // Use token in API headers
     * $headers = [
     *     'Authorization' => 'Bearer ' . $token,
     *     'Content-Type' => 'application/json'
     * ];
     */
    public function getAccessToken()
    {
        if ($this->isTokenValid()) {
            return $this->accessToken;
        }

        // Try to get from cache first
        $cachedToken = $this->getCachedToken();
        if ($cachedToken && $this->isTokenValid($cachedToken['token'], $cachedToken['expiry'])) {
            $this->accessToken = $cachedToken['token'];
            $this->tokenExpiry = $cachedToken['expiry'];
            return $this->accessToken;
        }

        return $this->requestNewToken();
    }

    /**
     * Validate token expiry with safety margin
     * 
     * Checks if the provided token is still valid, considering a safety margin
     * to prevent using tokens that are about to expire. This ensures API requests
     * don't fail due to token expiry during request processing.
     * 
     * @param string|null $token Access token to validate (optional, uses current token if not provided)
     * @param int|null $expiry Token expiry timestamp (optional, uses current expiry if not provided)
     * 
     * @return bool True if token is valid and not expired, false otherwise
     */
    private function isTokenValid($token = null, $expiry = null)
    {
        $token = $token ?: $this->accessToken;
        $expiry = $expiry ?: $this->tokenExpiry;

        if (!$token || !$expiry) {
            return false;
        }

        // Consider token expired 60 seconds before actual expiry
        return time() < $expiry - 60;
    }

    /**
     * Retrieve cached token from storage
     * 
     * @return array|null Cached token data containing 'token' and 'expiry', or null if not found
     */
    private function getCachedToken()
    {
        $cacheKey = $this->getCacheKey();
        return $this->cache->get($cacheKey);
    }

    /**
     * Store token in cache with calculated TTL
     * 
     * Caches the access token with a time-to-live that accounts for the
     * token's actual expiry time minus a safety buffer to ensure tokens
     * are refreshed before they expire.
     * 
     * @param string $token Access token to cache
     * @param int $expiry Token expiry timestamp
     * 
     * @return void
     */
    private function cacheToken($token, $expiry)
    {
        $cacheKey = $this->getCacheKey();
        $ttl = $expiry - time() - 120; // Cache TTL with 2 minutes buffer

        $this->cache->set($cacheKey, [
            'token' => $token,
            'expiry' => $expiry
        ], $ttl);
    }

    /**
     * Generate unique cache key for token storage
     * 
     * Creates a cache key based on client credentials to ensure token isolation
     * between different client configurations. This allows multiple SDK instances
     * with different credentials to coexist without token conflicts.
     * 
     * @return string Unique cache key for token storage
     */
    private function getCacheKey()
    {
        return self::CACHE_KEY_PREFIX . md5($this->config->getClientId() . $this->config->getClientSecret());
    }

    /**
     * Request new access token from SingaPay authentication API
     * 
     * Performs OAuth2 client credentials grant flow to obtain a new access token.
     * Includes cryptographic signature generation for request authentication
     * and handles API response parsing and validation.
     * 
     * @return string New access token
     * 
     * @throws AuthenticationException When token request fails, API returns error,
     *                                or response format is invalid
     * @throws \RuntimeException When HTTP client is not available
     */
    private function requestNewToken()
    {
        if ($this->client === null) {
            throw new \RuntimeException('HTTP client must be set before requesting tokens');
        }

        $signature = Signature::generateV11(
            $this->config->getClientId(),
            $this->config->getClientSecret()
        );

        $headers = [
            'Accept' => 'application/json',
            'X-PARTNER-ID' => $this->config->getApiKey(),
            'X-CLIENT-ID' => $this->config->getClientId(),
            'X-Signature' => $signature,
            'Content-Type' => 'application/json'
        ];

        $body = [
            'grant_type' => 'client_credentials'
        ];

        try {
            $response = $this->client->post('/api/v1.1/access-token/b2b', $body, $headers);

            if (!$response->isSuccess()) {
                throw new AuthenticationException(
                    $response->getMessage() ?? 'Failed to obtain access token',
                    $response->getCode()
                );
            }

            $data = $response->getData();

            if (!isset($data['access_token'])) {
                throw new AuthenticationException('Access token not found in response');
            }

            $this->accessToken = $data['access_token'];
            $this->tokenExpiry = time() + ($data['expires_in'] ?? 3600);

            // Cache the token
            $this->cacheToken($this->accessToken, $this->tokenExpiry);

            return $this->accessToken;
        } catch (\Exception $e) {
            throw new AuthenticationException('Authentication failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Force token refresh and clear cache
     * 
     * Invalidates current token and cached token, then requests a new token
     * from the authentication API. Useful when tokens need to be revoked
     * or when switching security contexts.
     * 
     * @return string New access token
     * 
     * @throws AuthenticationException When token refresh fails
     * 
     * @example
     * // Force token refresh (e.g., after credential rotation)
     * $newToken = $auth->refreshToken();
     * 
     * // Subsequent API calls will use the new token
     * $accounts = $apiClient->getAccounts();
     */
    public function refreshToken()
    {
        $this->accessToken = null;
        $this->tokenExpiry = null;

        // Clear cached token
        $cacheKey = $this->getCacheKey();
        $this->cache->delete($cacheKey);

        return $this->requestNewToken();
    }

    /**
     * Set custom cache implementation
     * 
     * Allows replacing the default cache implementation with a custom one
     * (e.g., Redis, Memcached, database). The cache implementation must
     * implement the CacheInterface contract.
     * 
     * @param CacheInterface $cache Custom cache implementation
     * 
     * @return self Returns Authentication instance for method chaining
     * 
     * @example
     * // Use Redis for token caching
     * $redisCache = new RedisCache($redisClient);
     * $auth->setCache($redisCache);
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
        return $this;
    }
}
