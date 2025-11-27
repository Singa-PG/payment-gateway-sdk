/**
 * Environment Constants
 *
 * Defines the available environments for SingaPay API integration.
 *
 * @constant {string} ENV_SANDBOX Sandbox environment for testing
 * @constant {string} ENV_PRODUCTION Production environment for live transactions
 */
export const ENV_SANDBOX = "sandbox";
export const ENV_PRODUCTION = "production";

/**
 * Default Configuration Constants
 *
 * Standard default values for SDK configuration parameters.
 * These values are used when no explicit configuration is provided.
 *
 * @constant {number} DEFAULT_TIMEOUT Default request timeout in seconds
 * @constant {number} DEFAULT_MAX_RETRIES Default maximum retry attempts
 * @constant {number} DEFAULT_RETRY_DELAY Default retry delay in milliseconds
 * @constant {boolean} DEFAULT_AUTO_REAUTH Default auto reauthentication setting
 * @constant {number} DEFAULT_CACHE_TTL Default cache time-to-live in seconds
 */
export const DEFAULT_TIMEOUT = 30; // seconds
export const DEFAULT_MAX_RETRIES = 3; // times
export const DEFAULT_RETRY_DELAY = 1000; // milliseconds
export const DEFAULT_AUTO_REAUTH = true; // boolean
export const DEFAULT_CACHE_TTL = 3600; // seconds

/**
 * API Base URL Constants
 *
 * Defines the base URLs for SingaPay API endpoints across different environments.
 *
 * @constant {string} SANDBOX_URL Base URL for sandbox environment
 * @constant {string} PRODUCTION_URL Base URL for production environment
 */
export const SANDBOX_URL = "https://sandbox-payment-b2b.singapay.id";
export const PRODUCTION_URL = "https://payment-b2b.singapay.id";
