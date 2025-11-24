<?php

namespace SingaPay\Http;

/**
 * SingaPay HTTP Interceptor Interface
 * 
 * Defines the contract for HTTP interceptors that can monitor and modify
 * API requests and responses within the SingaPay SDK. Interceptors follow
 * the chain of responsibility pattern and allow for cross-cutting concerns
 * like logging, metrics, authentication, and request transformation.
 * 
 * Implement this interface to create custom interceptors that can:
 * - Log request and response data
 * - Collect performance metrics
 * - Modify request headers or body
 * - Handle authentication and retry logic
 * - Implement caching strategies
 * - Monitor API usage and quotas
 * 
 * @package SingaPay\Http
 * @author PT. Abadi Singapay Indonesia
  */
interface InterceptorInterface
{
    /**
     * Handle HTTP request before sending to API
     * 
     * Called immediately before an HTTP request is dispatched to the SingaPay API.
     * This method can be used to:
     * - Log request details for auditing
     * - Modify request headers or body
     * - Add authentication tokens
     * - Start performance timers
     * - Validate request parameters
     * - Implement request rate limiting
     * 
     * @param string $method HTTP method (GET, POST, PUT, DELETE, PATCH)
     * @param string $endpoint API endpoint path (e.g., '/api/v1.0/accounts')
     * @param array $options Request options including:
     *                      - headers: Array of HTTP headers
     *                      - json: Request body data (for POST, PUT, PATCH)
     *                      - query: Query parameters (for GET requests)
     *                      - timeout: Request timeout in seconds
     * 
     * @return void
     * 
     * @example
     * public function request($method, $endpoint, $options)
     * {
     *     // Add custom header to all requests
     *     $options['headers']['X-Custom-Header'] = 'CustomValue';
     *     
     *     // Log request details
     *     $this->logger->info("Outgoing API request", [
     *         'method' => $method,
     *         'endpoint' => $endpoint,
     *         'headers' => array_keys($options['headers'])
     *     ]);
     * }
     */
    public function request($method, $endpoint, $options);

    /**
     * Handle HTTP response after receiving from API
     * 
     * Called immediately after receiving a successful HTTP response from the
     * SingaPay API. This method can be used to:
     * - Log response details and performance metrics
     * - Parse and transform response data
     * - Update authentication tokens from response headers
     * - Implement response caching
     * - Validate response structure and data
     * - Track API usage statistics
     * 
     * @param string $method HTTP method used for the request
     * @param string $endpoint API endpoint that was called
     * @param array $options Request options that were used
     * @param Response $response Response object containing:
     *                          - statusCode: HTTP status code
     *                          - body: Decoded response body
     *                          - success: Boolean indicating success status
     * 
     * @return void
     * 
     * @example
     * public function response($method, $endpoint, $options, $response)
     * {
     *     // Log successful responses
     *     $this->logger->info("API response received", [
     *         'method' => $method,
     *         'endpoint' => $endpoint,
     *         'status_code' => $response->getStatusCode(),
     *         'success' => $response->isSuccess()
     *     ]);
     *     
     *     // Cache successful GET responses
     *     if ($method === 'GET' && $response->isSuccess()) {
     *         $this->cache->set($endpoint, $response->getData(), 300);
     *     }
     * }
     */
    public function response($method, $endpoint, $options, $response);

    /**
     * Handle HTTP request errors and exceptions
     * 
     * Called when an HTTP request fails due to network issues, timeouts,
     * or API errors (HTTP 4xx/5xx responses). This method can be used to:
     * - Log error details for debugging
     * - Implement custom retry logic
     * - Handle specific error conditions
     * - Notify monitoring systems
     * - Transform error responses
     * - Implement circuit breaker patterns
     * 
     * @param string $method HTTP method used for the failed request
     * @param string $endpoint API endpoint that was called
     * @param array $options Request options that were used
     * @param \Exception $exception Exception thrown during the request, which may be:
     *                             - ApiException: API returned error response
     *                             - AuthenticationException: Auth failed
     *                             - ValidationException: Request validation failed
     *                             - \Exception: Network or other errors
     * 
     * @return void
     * 
     * @example
     * public function error($method, $endpoint, $options, $exception)
     * {
     *     // Log error details
     *     $this->logger->error("API request failed", [
     *         'method' => $method,
     *         'endpoint' => $endpoint,
     *         'error' => $exception->getMessage(),
     *         'code' => $exception->getCode()
     *     ]);
     *     
     *     // Implement circuit breaker for repeated failures
     *     if ($exception->getCode() >= 500) {
     *         $this->circuitBreaker->recordFailure($endpoint);
     *     }
     * }
     */
    public function error($method, $endpoint, $options, $exception);
}
