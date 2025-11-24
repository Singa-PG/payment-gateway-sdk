<?php

namespace SingaPay;

/**
 * SingaPay Constants Definition
 * 
 * Centralized configuration constants for the SingaPay PHP SDK.
 * This class defines all environment settings, default values, and API endpoints
 * used throughout the SDK for consistent configuration management.
 * 
 * @package SingaPay
 * @author PT. Abadi Singapay Indonesia
 */
class Constant
{
    /**
     * Environment Configuration
     * 
     * Defines the available operating environments for the SingaPay SDK.
     * Partners should use 'sandbox' for testing and development, and 
     * 'production' for live transaction processing.
     */

    /**
     * Sandbox environment constant
     * 
     * Use this environment for testing, development, and integration verification.
     * Sandbox environment provides mock responses and does not process real transactions.
     * All transactions in sandbox are simulated and no actual funds are transferred.
     * 
     * @var string
     */
    const ENV_SANDBOX = 'sandbox';

    /**
     * Production environment constant
     * 
     * Use this environment for live production systems processing real transactions.
     * Production environment connects to live banking systems and processes actual payments.
     * All transactions in production involve real fund transfers and financial settlements.
     * 
     * @var string
     */
    const ENV_PRODUCTION = 'production';

    /**
     * Default Configuration Values
     * 
     * These constants define the default behavior and settings for the SDK
     * when no explicit configuration is provided by the partner.
     */

    /**
     * Default HTTP request timeout in seconds
     * 
     * Maximum time in seconds to wait for an API response before timing out.
     * This value applies to all HTTP requests made by the SDK to SingaPay servers.
     * Adjust this value based on network conditions and expected API response times.
     * 
     * @var int
     */
    const DEFAULT_TIMEOUT = 30;

    /**
     * Default maximum retry attempts for failed requests
     * 
     * Number of automatic retry attempts for failed API requests before giving up.
     * Retries are automatically performed for temporary failures, network issues,
     * and server errors (5xx HTTP status codes).
     * 
     * @var int
     */
    const DEFAULT_MAX_RETRIES = 3;

    /**
     * Default retry delay in milliseconds
     * 
     * Initial delay between retry attempts in milliseconds. The SDK implements
     * exponential backoff, so subsequent retries will have increasing delays.
     * This helps prevent overwhelming the API during temporary service disruptions.
     * 
     * @var int
     */
    const DEFAULT_RETRY_DELAY = 1000;

    /**
     * Default automatic re-authentication setting
     * 
     * When enabled, the SDK automatically refreshes expired access tokens
     * without requiring manual intervention. This ensures continuous operation
     * even when authentication tokens expire during extended sessions.
     * 
     * @var bool
     */
    const DEFAULT_AUTO_REAUTH = true;

    /**
     * Default cache time-to-live in seconds
     * 
     * Duration in seconds that authentication tokens are cached locally.
     * Caching tokens reduces API calls and improves performance. The TTL
     * is set slightly shorter than the actual token expiry to ensure tokens
     * are refreshed before they expire.
     * 
     * @var int
     */
    const DEFAULT_CACHE_TTL = 3600;

    /**
     * API Base URLs
     * 
     * These constants define the base URLs for SingaPay API endpoints.
     * Partners should ensure their systems can reach these endpoints
     * and that appropriate firewall rules are configured.
     */

    /**
     * Sandbox environment base URL
     * 
     * The base URL for SingaPay's sandbox/testing environment.
     * All API requests in sandbox mode are directed to this endpoint.
     * This environment is isolated from production systems and is intended
     * for partner development, testing, and integration verification.
     * 
     * @var string
     */
    const SANDBOX_URL = 'https://sandbox-payment-b2b.singapay.id';

    /**
     * Production environment base URL
     * 
     * The base URL for SingaPay's live production environment.
     * All API requests in production mode are directed to this endpoint.
     * This environment processes real financial transactions and connects
     * to live banking systems. Partners must complete certification and
     * receive approval before using this endpoint.
     * 
     * @var string
     */
    const PRODUCTION_URL = 'https://payment-b2b.singapay.id';
}
