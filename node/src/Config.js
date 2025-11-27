import {
  DEFAULT_AUTO_REAUTH,
  DEFAULT_CACHE_TTL,
  DEFAULT_MAX_RETRIES,
  DEFAULT_RETRY_DELAY,
  DEFAULT_TIMEOUT,
  ENV_PRODUCTION,
  ENV_SANDBOX,
  PRODUCTION_URL,
  SANDBOX_URL,
} from "./constants.js";

/**
 * Config - SingaPay SDK Configuration Manager
 *
 * Centralized configuration management for the SingaPay SDK. Handles all
 * configuration parameters including authentication credentials, API endpoints,
 * timeouts, retry policies, and caching settings.
 *
 * Provides validation, default values, and fluent interface for configuration
 * updates. Supports both camelCase and snake_case property names for
 * compatibility with different coding standards.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class Config {
  /**
   * Creates a new Config instance
   *
   * @param {object} config Configuration object with authentication and behavior settings
   * @param {string} config.clientId Client identifier provided by SingaPay (required)
   * @param {string} config.clientSecret Client secret key provided by SingaPay (required)
   * @param {string} config.apiKey API key provided by SingaPay (required)
   * @param {string} [config.hmacValidationKey] HMAC validation key for webhook verification
   * @param {string} [config.environment=sandbox] API environment: 'sandbox' or 'production'
   * @param {string} [config.baseUrl] Custom base URL (defaults based on environment)
   * @param {number} [config.timeout=30] Request timeout in seconds
   * @param {number} [config.maxRetries=3] Maximum number of retry attempts
   * @param {number} [config.retryDelay=1000] Base retry delay in milliseconds
   * @param {boolean} [config.autoReauth=true] Enable automatic token refresh on auth failures
   * @param {number} [config.cacheTtl=300] Default cache TTL in seconds
   * @param {object} [config.customHeaders={}] Additional HTTP headers for all requests
   *
   * @example
   * // Minimal configuration
   * const config = new Config({
   *   clientId: 'your-client-id',
   *   clientSecret: 'your-client-secret',
   *   apiKey: 'your-api-key'
   * });
   *
   * // Full configuration
   * const config = new Config({
   *   clientId: 'your-client-id',
   *   clientSecret: 'your-client-secret',
   *   apiKey: 'your-api-key',
   *   environment: 'production',
   *   timeout: 60,
   *   maxRetries: 5,
   *   customHeaders: {
   *     'X-Custom-Header': 'value'
   *   }
   * });
   *
   * @throws {Error} When required fields are missing or validation fails
   */
  constructor(config = {}) {
    this.clientId = config.clientId || config.client_id || null;
    this.clientSecret = config.clientSecret || config.client_secret || null;
    this.apiKey = config.apiKey || config.api_key || null;
    this.hmacValidationKey =
      config.hmacValidationKey || config.hmac_validation_key || null;

    this.environment = config.environment || ENV_SANDBOX;
    this.baseUrl =
      config.baseUrl || config.base_url || this.getDefaultBaseUrl();

    this.timeout = config.timeout || DEFAULT_TIMEOUT;
    this.maxRetries =
      config.maxRetries || config.max_retries || DEFAULT_MAX_RETRIES;
    this.retryDelay =
      config.retryDelay || config.retry_delay || DEFAULT_RETRY_DELAY;
    this.autoReauth =
      config.autoReauth ?? config.auto_reauth ?? DEFAULT_AUTO_REAUTH;
    this.cacheTtl = config.cacheTtl || config.cache_ttl || DEFAULT_CACHE_TTL;

    this.customHeaders = config.customHeaders || config.custom_headers || {};

    this.validate();
  }

  /**
   * Validate configuration parameters
   *
   * Checks for required fields and validates parameter values.
   * Called automatically during construction.
   *
   * @private
   * @throws {Error} When validation fails
   */
  validate() {
    const required = ["clientId", "clientSecret", "apiKey"];

    for (const field of required) {
      if (!this[field]) {
        throw new Error(`Config field '${field}' is required`);
      }
    }

    if (![ENV_SANDBOX, ENV_PRODUCTION].includes(this.environment)) {
      throw new Error("Invalid environment. Must be 'sandbox' or 'production'");
    }

    if (this.maxRetries < 0) {
      throw new Error("Max retries must be non-negative");
    }
  }

  /**
   * Get default base URL based on environment
   *
   * @private
   * @returns {string} Base URL for the configured environment
   */
  getDefaultBaseUrl() {
    return this.environment === ENV_PRODUCTION ? PRODUCTION_URL : SANDBOX_URL;
  }

  /**
   * Get client ID
   *
   * @returns {string} Client identifier
   */
  getClientId() {
    return this.clientId;
  }

  /**
   * Get client secret
   *
   * @returns {string} Client secret key
   */
  getClientSecret() {
    return this.clientSecret;
  }

  /**
   * Get API key
   *
   * @returns {string} API key
   * @throws {Error} If API key is not configured
   */
  getApiKey() {
    if (!this.apiKey) {
      throw new Error("API Key is not configured");
    }
    return this.apiKey;
  }

  /**
   * Get HMAC validation key
   *
   * @returns {string|null} HMAC validation key for webhook verification
   */
  getHmacValidationKey() {
    return this.hmacValidationKey;
  }

  /**
   * Get base URL
   *
   * @returns {string} Base URL for API requests
   */
  getBaseUrl() {
    return this.baseUrl;
  }

  /**
   * Get request timeout
   *
   * @returns {number} Timeout in seconds
   */
  getTimeout() {
    return this.timeout;
  }

  /**
   * Get maximum retry attempts
   *
   * @returns {number} Maximum number of retry attempts
   */
  getMaxRetries() {
    return this.maxRetries;
  }

  /**
   * Get base retry delay
   *
   * @returns {number} Base retry delay in milliseconds
   */
  getRetryDelay() {
    return this.retryDelay;
  }

  /**
   * Check if auto reauthentication is enabled
   *
   * @returns {boolean} True if automatic token refresh is enabled
   */
  isAutoReauthEnabled() {
    return this.autoReauth;
  }

  /**
   * Get default cache TTL
   *
   * @returns {number} Default cache time-to-live in seconds
   */
  getCacheTtl() {
    return this.cacheTtl;
  }

  /**
   * Get custom headers
   *
   * @returns {object} Object containing custom HTTP headers
   */
  getCustomHeaders() {
    return this.customHeaders;
  }

  /**
   * Check if production environment is configured
   *
   * @returns {boolean} True if environment is production
   */
  isProduction() {
    return this.environment === ENV_PRODUCTION;
  }

  /**
   * Set request timeout
   *
   * @param {number} timeout Timeout in seconds
   * @returns {Config} Returns this for method chaining
   */
  setTimeout(timeout) {
    this.timeout = timeout;
    return this;
  }

  /**
   * Set maximum retry attempts
   *
   * @param {number} maxRetries Maximum number of retry attempts
   * @returns {Config} Returns this for method chaining
   */
  setMaxRetries(maxRetries) {
    this.maxRetries = maxRetries;
    return this;
  }

  /**
   * Set base retry delay
   *
   * @param {number} retryDelay Base retry delay in milliseconds
   * @returns {Config} Returns this for method chaining
   */
  setRetryDelay(retryDelay) {
    this.retryDelay = retryDelay;
    return this;
  }

  /**
   * Enable or disable auto reauthentication
   *
   * @param {boolean} enabled True to enable automatic token refresh
   * @returns {Config} Returns this for method chaining
   */
  setAutoReauth(enabled) {
    this.autoReauth = enabled;
    return this;
  }

  /**
   * Set default cache TTL
   *
   * @param {number} ttl Cache time-to-live in seconds
   * @returns {Config} Returns this for method chaining
   */
  setCacheTtl(ttl) {
    this.cacheTtl = ttl;
    return this;
  }

  /**
   * Add custom HTTP header
   *
   * @param {string} name Header name
   * @param {string} value Header value
   * @returns {Config} Returns this for method chaining
   */
  addCustomHeader(name, value) {
    this.customHeaders[name] = value;
    return this;
  }

  /**
   * Remove custom HTTP header
   *
   * @param {string} name Header name to remove
   * @returns {Config} Returns this for method chaining
   */
  removeCustomHeader(name) {
    delete this.customHeaders[name];
    return this;
  }

  /**
   * Convert configuration to safe object representation
   *
   * Returns a sanitized object with sensitive fields masked for logging
   * and debugging purposes.
   *
   * @returns {object} Safe configuration object with masked sensitive data
   *
   * @example
   * console.log(config.toObject());
   * // Output: {
   * //   clientId: 'client123',
   * //   clientSecret: '***HIDDEN***',
   * //   apiKey: 'apikey12...',
   * //   environment: 'sandbox',
   * //   baseUrl: 'https://sandbox-api.singapay.com',
   * //   timeout: 30,
   * //   maxRetries: 3,
   * //   retryDelay: 1000,
   * //   autoReauth: true,
   * //   cacheTtl: 300
   * // }
   */
  toObject() {
    return {
      clientId: this.clientId,
      clientSecret: "***HIDDEN***",
      apiKey: this.apiKey.substring(0, 8) + "...",
      environment: this.environment,
      baseUrl: this.baseUrl,
      timeout: this.timeout,
      maxRetries: this.maxRetries,
      retryDelay: this.retryDelay,
      autoReauth: this.autoReauth,
      cacheTtl: this.cacheTtl,
    };
  }
}
