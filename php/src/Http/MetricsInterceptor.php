<?php

namespace SingaPay\Http;

use SingaPay\Http\InterceptorInterface;

/**
 * SingaPay Metrics Interceptor
 * 
 * Collects and aggregates performance metrics and statistics for API requests
 * made through the SingaPay SDK. This interceptor tracks request counts,
 * success/failure rates, response times, and other operational metrics
 * for monitoring and performance analysis.
 * 
 * @package SingaPay\Http
 * @author PT. Abadi Singapay Indonesia
  */
class MetricsInterceptor implements InterceptorInterface
{
    /**
     * @var array Collected metrics and statistics
     * 
     * Metrics structure:
     * - total_requests: Total number of API requests made
     * - successful_requests: Number of requests that completed successfully
     * - failed_requests: Number of requests that resulted in errors
     * - total_response_time: Cumulative response time in seconds for all requests
     * - last_request_time: Timestamp of the most recent request
     */
    private $metrics = [
        'total_requests' => 0,
        'successful_requests' => 0,
        'failed_requests' => 0,
        'total_response_time' => 0,
        'last_request_time' => null,
    ];

    /**
     * Intercept outgoing HTTP requests
     * 
     * Called before each HTTP request is sent to the SingaPay API.
     * Increments the total request counter and records the request start time.
     * 
     * @param string $method HTTP method (GET, POST, PUT, DELETE, PATCH)
     * @param string $endpoint API endpoint path
     * @param array $options Request options including headers and body
     * 
     * @return void
     * 
     * @example
     * // This method is automatically called by the HTTP client
     * // before each API request to track request initiation
     */
    public function request($method, $endpoint, $options)
    {
        $this->metrics['total_requests']++;
        $this->metrics['last_request_time'] = microtime(true);
    }

    /**
     * Intercept successful HTTP responses
     * 
     * Called after receiving a successful HTTP response from the SingaPay API.
     * Increments the successful request counter and calculates response time
     * for performance monitoring.
     * 
     * @param string $method HTTP method used for the request
     * @param string $endpoint API endpoint that was called
     * @param array $options Request options that were used
     * @param Response $response Successful response object
     * 
     * @return void
     * 
     * @example
     * // This method is automatically called by the HTTP client
     * // after receiving a successful API response (HTTP 2xx)
     */
    public function response($method, $endpoint, $options, $response)
    {
        $this->metrics['successful_requests']++;

        // Calculate response time if last_request_time is available
        if ($this->metrics['last_request_time'] !== null) {
            $responseTime = microtime(true) - $this->metrics['last_request_time'];
            $this->metrics['total_response_time'] += $responseTime;
        }
    }

    /**
     * Intercept HTTP request errors
     * 
     * Called when an HTTP request fails or returns an error response.
     * Increments the failed request counter and includes errors from
     * network failures, timeouts, and API error responses (HTTP 4xx/5xx).
     * 
     * @param string $method HTTP method used for the failed request
     * @param string $endpoint API endpoint that was called
     * @param array $options Request options that were used
     * @param \Exception $exception Exception thrown during the request
     * 
     * @return void
     * 
     * @example
     * // This method is automatically called by the HTTP client
     * // when a request fails due to network issues or API errors
     */
    public function error($method, $endpoint, $options, $exception)
    {
        $this->metrics['failed_requests']++;

        // Calculate response time for failed requests as well
        if ($this->metrics['last_request_time'] !== null) {
            $responseTime = microtime(true) - $this->metrics['last_request_time'];
            $this->metrics['total_response_time'] += $responseTime;
        }
    }

    /**
     * Get collected metrics and statistics
     * 
     * Returns all accumulated metrics since the interceptor was instantiated.
     * This includes request counts, success rates, and timing information
     * useful for monitoring, alerting, and performance analysis.
     * 
     * @return array Metrics data containing:
     *               - total_requests: Total number of API requests
     *               - successful_requests: Number of successful requests
     *               - failed_requests: Number of failed requests
     *               - total_response_time: Cumulative response time in seconds
     *               - last_request_time: Timestamp of last request
     * 
     * @example
     * $metrics = $interceptor->getMetrics();
     * 
     * echo "Total Requests: " . $metrics['total_requests'] . "\n";
     * echo "Successful: " . $metrics['successful_requests'] . "\n";
     * echo "Failed: " . $metrics['failed_requests'] . "\n";
     * echo "Success Rate: " . 
     *      ($metrics['successful_requests'] / $metrics['total_requests'] * 100) . "%\n";
     */
    public function getMetrics()
    {
        return $this->metrics;
    }

    /**
     * Calculate average response time
     * 
     * Computes the average response time across all completed requests
     * (both successful and failed). Returns 0 if no requests have been made.
     * This metric helps identify performance trends and API responsiveness.
     * 
     * @return float Average response time in seconds
     * 
     * @example
     * $avgResponseTime = $interceptor->getAverageResponseTime();
     * 
     * if ($avgResponseTime > 2.0) {
     *     // Alert on slow API performance
     *     $this->alertSlowPerformance($avgResponseTime);
     * }
     * 
     * echo "Average API response time: " . 
     *      number_format($avgResponseTime * 1000, 2) . " ms\n";
     */
    public function getAverageResponseTime()
    {
        if ($this->metrics['total_requests'] === 0) {
            return 0;
        }

        return $this->metrics['total_response_time'] / $this->metrics['total_requests'];
    }
}
