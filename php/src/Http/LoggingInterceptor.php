<?php

namespace SingaPay\Http;

use Psr\Log\LoggerInterface;
use SingaPay\Http\InterceptorInterface;

/**
 * SingaPay Logging Interceptor
 * 
 * Provides comprehensive logging for all API interactions with SingaPay services.
 * This interceptor logs request details, response outcomes, and errors using
 * PSR-3 compatible logging systems. Supports integration with Monolog, Laravel
 * Log, and other PSR-3 compliant logging implementations.
 * 
 * @package SingaPay\Http
 * @author PT. Abadi Singapay Indonesia
  */
class LoggingInterceptor implements InterceptorInterface
{
    /**
     * @var LoggerInterface|null PSR-3 compatible logger instance
     */
    private $logger;

    /**
     * Initialize Logging Interceptor
     * 
     * Creates a new logging interceptor with an optional PSR-3 logger instance.
     * If no logger is provided, logging operations will be no-ops. This allows
     * for flexible deployment where logging may be optional.
     * 
     * @param LoggerInterface|null $logger PSR-3 compatible logger instance
     * 
     * @example
     * // With Monolog logger
     * $logger = new \Monolog\Logger('singapay');
     * $logger->pushHandler(new StreamHandler('path/to/singapay.log'));
     * $interceptor = new LoggingInterceptor($logger);
     * 
     * // With Laravel logger
     * $interceptor = new LoggingInterceptor(app('log'));
     * 
     * // Without logger (no logging)
     * $interceptor = new LoggingInterceptor();
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Log outgoing API requests
     * 
     * Records details of HTTP requests before they are sent to SingaPay API.
     * Logs include HTTP method, endpoint, request headers, and body presence
     * for audit trails and request debugging.
     * 
     * @param string $method HTTP method (GET, POST, PUT, DELETE, PATCH)
     * @param string $endpoint API endpoint path being called
     * @param array $options Request options including headers and body data
     * 
     * @return void
     * 
     * @example
     * // Log output example:
     * // [2024-01-15 10:30:00] singapay.INFO: SingaPay API Request
     * // {
     * //   "method": "POST",
     * //   "endpoint": "/api/v1.0/accounts",
     * //   "headers": ["X-PARTNER-ID", "Authorization", "Content-Type"],
     * //   "has_body": true
     * // }
     */
    public function request($method, $endpoint, $options)
    {
        if ($this->logger) {
            $this->logger->info("SingaPay API Request", [
                'method' => $method,
                'endpoint' => $endpoint,
                'headers' => array_keys($options['headers'] ?? []),
                'has_body' => !empty($options['json']),
            ]);
        }
    }

    /**
     * Log successful API responses
     * 
     * Records details of successful HTTP responses from SingaPay API.
     * Logs include HTTP method, endpoint, status code, and success status
     * for response monitoring and performance analysis.
     * 
     * @param string $method HTTP method used for the request
     * @param string $endpoint API endpoint that was called
     * @param array $options Request options that were used
     * @param Response $response Response object containing status and data
     * 
     * @return void
     * 
     * @example
     * // Log output example:
     * // [2024-01-15 10:30:01] singapay.INFO: SingaPay API Response
     * // {
     * //   "method": "POST",
     * //   "endpoint": "/api/v1.0/accounts",
     * //   "status_code": 201,
     * //   "success": true
     * // }
     */
    public function response($method, $endpoint, $options, $response)
    {
        if ($this->logger) {
            $this->logger->info("SingaPay API Response", [
                'method' => $method,
                'endpoint' => $endpoint,
                'status_code' => $response->getStatusCode(),
                'success' => $response->isSuccess(),
            ]);
        }
    }

    /**
     * Log API errors and exceptions
     * 
     * Records details of failed API requests including network errors,
     * HTTP errors, and API-level errors. Provides comprehensive error
     * information for debugging and issue resolution.
     * 
     * @param string $method HTTP method used for the failed request
     * @param string $endpoint API endpoint that was called
     * @param array $options Request options that were used
     * @param \Exception $exception Exception thrown during the request
     * 
     * @return void
     * 
     * @example
     * // Log output example:
     * // [2024-01-15 10:30:00] singapay.ERROR: SingaPay API Error
     * // {
     * //   "method": "POST",
     * //   "endpoint": "/api/v1.0/accounts",
     * //   "error": "Authentication failed: Invalid client credentials",
     * //   "code": 401
     * // }
     */
    public function error($method, $endpoint, $options, $exception)
    {
        if ($this->logger) {
            $this->logger->error("SingaPay API Error", [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ]);
        }
    }
}
