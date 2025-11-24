<?php

namespace SingaPay\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use SingaPay\Config;
use SingaPay\Exceptions\ApiException;
use SingaPay\Exceptions\AuthenticationException;
use SingaPay\Exceptions\ValidationException;
use SingaPay\Security\Authentication;

/**
 * SingaPay HTTP Client
 * 
 * Core HTTP client for communicating with SingaPay API endpoints. This class
 * provides a robust, feature-rich HTTP client with automatic retry mechanisms,
 * comprehensive error handling, interceptor support, and seamless authentication
 * integration. Built on GuzzleHTTP with enhanced enterprise capabilities.
 * 
 * @package SingaPay\Http
 * @author PT. Abadi Singapay Indonesia
  */
class Client
{
    /**
     * @var Config SDK configuration instance
     */
    private $config;

    /**
     * @var GuzzleClient Underlying Guzzle HTTP client instance
     */
    private $httpClient;

    /**
     * @var Authentication Authentication handler for token management
     */
    private $auth;

    /**
     * @var array Collection of registered interceptors
     */
    private $interceptors = [];

    /**
     * Initialize HTTP Client
     * 
     * Constructs a new HTTP client with the provided configuration and
     * authentication handler. Automatically configures the underlying
     * Guzzle client with appropriate base URL, timeout, and SSL settings.
     * Registers default interceptors for logging and metrics collection.
     * 
     * @param Config $config SDK configuration containing API settings
     * @param Authentication $auth Authentication handler for token management
     */
    public function __construct(Config $config, Authentication $auth)
    {
        $this->config = $config;
        $this->auth = $auth;

        $this->httpClient = new GuzzleClient([
            'base_uri' => $config->getBaseUrl(),
            'timeout' => $config->getTimeout(),
            'verify' => $config->isProduction()
        ]);

        // Add default interceptors
        $this->addInterceptor(new LoggingInterceptor());
        $this->addInterceptor(new MetricsInterceptor());
    }

    /**
     * Register HTTP interceptor
     * 
     * Adds an interceptor to the client's interceptor chain. Interceptors
     * are executed in the order they are added and can monitor or modify
     * requests and responses. Supports cross-cutting concerns like logging,
     * metrics, caching, and authentication.
     * 
     * @param InterceptorInterface $interceptor Interceptor instance to add
     * 
     * @return self Returns client instance for method chaining
     */
    public function addInterceptor(InterceptorInterface $interceptor)
    {
        $this->interceptors[] = $interceptor;
        return $this;
    }

    /**
     * Get registered interceptors
     * 
     * Returns all currently registered interceptors in the order they were added.
     * Useful for debugging, inspection, or custom interceptor management.
     * 
     * @return array Array of registered InterceptorInterface instances
     */
    public function getInterceptors()
    {
        return $this->interceptors;
    }

    /**
     * Execute GET request with automatic retry logic
     * 
     * Performs an HTTP GET request to the specified endpoint with automatic
     * retry capabilities for transient failures. Includes authentication
     * headers and executes all registered interceptors.
     * 
     * @param string $endpoint API endpoint path (e.g., '/api/v1.0/accounts')
     * @param array $headers Additional HTTP headers to include in the request
     * 
     * @return Response HTTP response object
     * 
     * @throws ApiException When request fails after all retry attempts
     * @throws AuthenticationException When authentication fails
     * @throws ValidationException When request validation fails
     */
    public function get($endpoint, array $headers = [])
    {
        return $this->requestWithRetry('GET', $endpoint, null, $headers);
    }

    /**
     * Execute POST request with automatic retry logic
     * 
     * Performs an HTTP POST request to the specified endpoint with automatic
     * retry capabilities. Supports request body and includes authentication
     * headers and interceptor execution.
     * 
     * @param string $endpoint API endpoint path
     * @param mixed $body Request body data (will be JSON encoded)
     * @param array $headers Additional HTTP headers to include
     * 
     * @return Response HTTP response object
     * 
     * @throws ApiException When request fails after all retry attempts
     * @throws AuthenticationException When authentication fails
     * @throws ValidationException When request validation fails
     */
    public function post($endpoint, $body = null, array $headers = [])
    {
        return $this->requestWithRetry('POST', $endpoint, $body, $headers);
    }

    /**
     * Execute PUT request with automatic retry logic
     * 
     * Performs an HTTP PUT request to the specified endpoint with automatic
     * retry capabilities. Used for updating existing resources with complete
     * replacement of the resource data.
     * 
     * @param string $endpoint API endpoint path
     * @param mixed $body Request body data (will be JSON encoded)
     * @param array $headers Additional HTTP headers to include
     * 
     * @return Response HTTP response object
     * 
     * @throws ApiException When request fails after all retry attempts
     * @throws AuthenticationException When authentication fails
     * @throws ValidationException When request validation fails
     */
    public function put($endpoint, $body = null, array $headers = [])
    {
        return $this->requestWithRetry('PUT', $endpoint, $body, $headers);
    }

    /**
     * Execute PATCH request with automatic retry logic
     * 
     * Performs an HTTP PATCH request to the specified endpoint with automatic
     * retry capabilities. Used for partial updates to existing resources
     * without requiring complete resource replacement.
     * 
     * @param string $endpoint API endpoint path
     * @param mixed $body Request body data (will be JSON encoded)
     * @param array $headers Additional HTTP headers to include
     * 
     * @return Response HTTP response object
     * 
     * @throws ApiException When request fails after all retry attempts
     * @throws AuthenticationException When authentication fails
     * @throws ValidationException When request validation fails
     */
    public function patch($endpoint, $body = null, array $headers = [])
    {
        return $this->requestWithRetry('PATCH', $endpoint, $body, $headers);
    }

    /**
     * Execute DELETE request with automatic retry logic
     * 
     * Performs an HTTP DELETE request to the specified endpoint with automatic
     * retry capabilities. Used for removing resources from the API.
     * 
     * @param string $endpoint API endpoint path
     * @param array $headers Additional HTTP headers to include
     * 
     * @return Response HTTP response object
     * 
     * @throws ApiException When request fails after all retry attempts
     * @throws AuthenticationException When authentication fails
     * @throws ValidationException When request validation fails
     */
    public function delete($endpoint, array $headers = [])
    {
        return $this->requestWithRetry('DELETE', $endpoint, null, $headers);
    }

    /**
     * Execute HTTP request with comprehensive retry logic
     * 
     * Implements sophisticated retry mechanism with exponential backoff for
     * transient failures. Handles authentication token refresh, server errors,
     * and rate limiting automatically. Continues retrying until maximum
     * retry count is reached or a non-retryable error occurs.
     * 
     * @param string $method HTTP method to execute
     * @param string $endpoint API endpoint path
     * @param mixed $body Request body data
     * @param array $headers Additional HTTP headers
     * 
     * @return Response HTTP response object
     * 
     * @throws ApiException When request fails after all retry attempts
     * @throws AuthenticationException When authentication cannot be refreshed
     * @throws ValidationException When request validation consistently fails
     */
    private function requestWithRetry($method, $endpoint, $body = null, array $headers = [])
    {
        $retryCount = 0;
        $maxRetries = $this->config->getMaxRetries();
        $lastException = null;

        while ($retryCount <= $maxRetries) {
            try {
                return $this->request($method, $endpoint, $body, $headers);
            } catch (AuthenticationException $e) {
                // For authentication errors, try to refresh token and retry
                if ($this->config->isAutoReauthEnabled() && $retryCount < $maxRetries) {
                    $this->auth->refreshToken();
                    $retryCount++;
                    usleep($this->config->getRetryDelay() * 1000);
                    continue;
                }
                throw $e;
            } catch (ApiException $e) {
                $lastException = $e;

                // Retry on server errors (5xx) and certain client errors (429 - Too Many Requests)
                if ($this->shouldRetry($e, $retryCount, $maxRetries)) {
                    $retryCount++;
                    usleep($this->config->getRetryDelay() * 1000 * $retryCount); // Exponential backoff
                    continue;
                }

                break;
            } catch (\Exception $e) {
                $lastException = $e;
                break;
            }
        }

        throw $lastException ?? new ApiException('Request failed after ' . $maxRetries . ' retries');
    }

    /**
     * Determine if failed request should be retried
     * 
     * Evaluates whether a failed request qualifies for automatic retry based
     * on error type, HTTP status code, and current retry count. Implements
     * intelligent retry logic for transient failures while avoiding retries
     * for permanent errors.
     * 
     * @param ApiException $exception Exception thrown by the failed request
     * @param int $retryCount Current retry attempt number
     * @param int $maxRetries Maximum allowed retry attempts
     * 
     * @return bool True if request should be retried, false otherwise
     */
    private function shouldRetry(ApiException $exception, $retryCount, $maxRetries)
    {
        if ($retryCount >= $maxRetries) {
            return false;
        }

        $statusCode = $exception->getCode();

        // Retry on server errors and rate limiting
        return ($statusCode >= 500 && $statusCode < 600) || $statusCode === 429;
    }

    /**
     * Execute single HTTP request with interceptor support
     * 
     * Performs a single HTTP request to the SingaPay API with comprehensive
     * interceptor execution, timing measurement, and exception handling.
     * Converts Guzzle exceptions to SingaPay-specific exceptions and
     * ensures proper interceptor notification for all request lifecycle events.
     * 
     * @param string $method HTTP method to execute
     * @param string $endpoint API endpoint path
     * @param mixed $body Request body data
     * @param array $headers Additional HTTP headers
     * 
     * @return Response HTTP response object
     * 
     * @throws ApiException For general API communication failures
     * @throws AuthenticationException For authentication-related failures
     * @throws ValidationException For request validation failures
     */
    private function request($method, $endpoint, $body = null, array $headers = [])
    {
        $options = [
            'headers' => array_merge($this->config->getCustomHeaders(), $headers)
        ];

        if ($body !== null) {
            $options['json'] = $body;
        }

        // Call request interceptors
        foreach ($this->interceptors as $interceptor) {
            $interceptor->request($method, $endpoint, $options);
        }

        $startTime = microtime(true);

        try {
            $response = $this->httpClient->request($method, $endpoint, $options);
            $responseTime = microtime(true) - $startTime;

            $singaPayResponse = new Response(
                $response->getStatusCode(),
                json_decode($response->getBody()->getContents(), true)
            );

            // Call response interceptors
            foreach ($this->interceptors as $interceptor) {
                $interceptor->response($method, $endpoint, $options, $singaPayResponse, $responseTime);
            }

            return $singaPayResponse;
        } catch (RequestException $e) {
            $responseTime = microtime(true) - $startTime;
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $body = $e->hasResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : [];

            $singaPayResponse = new Response($statusCode, $body);

            // Call error interceptors
            foreach ($this->interceptors as $interceptor) {
                $interceptor->error($method, $endpoint, $options, $e, $responseTime);
            }

            // Convert to appropriate exception
            if ($statusCode === 401) {
                throw new AuthenticationException(
                    'Authentication failed',
                    $statusCode,
                    $e->getPrevious()
                );
            } elseif ($statusCode === 422) {
                throw new ValidationException(
                    $singaPayResponse->getMessage() ?? 'Validation failed',
                    $body['errors'] ?? [],
                    $statusCode,
                    $e->getPrevious()
                );
            } else {
                throw new ApiException(
                    $singaPayResponse->getMessage() ?? 'API request failed',
                    $statusCode,
                    $e->getPrevious()
                );
            }
        } catch (\Exception $e) {
            $responseTime = microtime(true) - $startTime;

            // Call error interceptors
            foreach ($this->interceptors as $interceptor) {
                $interceptor->error($method, $endpoint, $options, $e, $responseTime);
            }

            throw new ApiException(
                'HTTP request failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Get underlying Guzzle HTTP client instance
     * 
     * Returns the raw GuzzleHTTP client instance for advanced use cases
     * requiring direct access to Guzzle's functionality. Use with caution
     * as direct usage bypasses SingaPay SDK's retry logic and interceptors.
     * 
     * @return GuzzleClient Underlying GuzzleHTTP client instance
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Retrieve metrics from registered interceptors
     * 
     * Extracts performance metrics and statistics from the MetricsInterceptor
     * if registered. Returns empty array if no metrics interceptor is found.
     * Useful for monitoring API performance and request patterns.
     * 
     * @return array Metrics data including request counts and timing information
     */
    public function getMetrics()
    {
        foreach ($this->interceptors as $interceptor) {
            if ($interceptor instanceof MetricsInterceptor) {
                return $interceptor->getMetrics();
            }
        }
        return [];
    }
}
