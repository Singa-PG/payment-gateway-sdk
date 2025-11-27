import axios from "axios";
import https from "https";
import {
  ApiException,
  AuthenticationException,
  ValidationException,
} from "../exceptions/SingaPayException.js";
import { Response } from "./Response.js";
import { LoggingInterceptor } from "./interceptors/LoggingInterceptor.js";
import { MetricsInterceptor } from "./interceptors/MetricsInterceptor.js";

/**
 * Client - HTTP Client for SingaPay API
 *
 * A robust HTTP client specifically designed for interacting with SingaPay APIs.
 * Provides automatic retry mechanisms, token refresh, request/response interception,
 * and comprehensive error handling. The client supports both production and sandbox
 * environments with configurable timeouts and security settings.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class Client {
  /**
   * Creates a new Client instance
   *
   * @param {object} config Configuration object containing API settings
   * @param {object} auth Authentication handler for token management
   *
   * @example
   * const config = new Config({ environment: 'sandbox', timeout: 30 });
   * const auth = new AuthHandler(config);
   * const client = new Client(config, auth);
   */
  constructor(config, auth) {
    /**
     * @private
     * @type {object}
     */
    this.config = config;
    /**
     * @private
     * @type {object}
     */
    this.auth = auth;
    /**
     * @private
     * @type {Array}
     */
    this.interceptors = [];
    /**
     * @private
     * @type {boolean}
     */
    this.isRefreshingToken = false;

    const axiosConfig = {
      baseURL: config.getBaseUrl(),
      timeout: config.getTimeout() * 1000,
    };

    if (!config.isProduction()) {
      axiosConfig.httpsAgent = new https.Agent({
        rejectUnauthorized: false,
      });
    }

    this.httpClient = axios.create(axiosConfig);

    // Default interceptors
    this.addInterceptor(new LoggingInterceptor());
    this.addInterceptor(new MetricsInterceptor());
  }

  /**
   * Add request/response interceptor
   *
   * Registers an interceptor that can modify requests, responses, or handle errors.
   * Interceptors are executed in the order they are added.
   *
   * @param {object} interceptor Interceptor object with request/response/error methods
   * @returns {Client} Returns this for method chaining
   *
   * @example
   * client.addInterceptor({
   *   request: async (method, endpoint, options) => {
   *     console.log(`Sending ${method} request to ${endpoint}`);
   *   },
   *   response: async (method, endpoint, options, response, responseTime) => {
   *     console.log(`Received response in ${responseTime}ms`);
   *   }
   * });
   */
  addInterceptor(interceptor) {
    this.interceptors.push(interceptor);
    return this;
  }

  /**
   * Get all registered interceptors
   *
   * @returns {Array} Array of registered interceptors
   *
   * @example
   * const interceptors = client.getInterceptors();
   * console.log(`Registered ${interceptors.length} interceptors`);
   */
  getInterceptors() {
    return this.interceptors;
  }

  /**
   * Send GET request
   *
   * @param {string} endpoint API endpoint path
   * @param {object} headers Additional HTTP headers (default: {})
   * @returns {Promise<Response>} Promise resolving to Response object
   *
   * @example
   * const response = await client.get('/v1/payments/123');
   * if (response.isSuccess()) {
   *   console.log('Payment data:', response.getData());
   * }
   */
  async get(endpoint, headers = {}) {
    return this.requestWithRetry("GET", endpoint, null, headers);
  }

  /**
   * Send POST request
   *
   * @param {string} endpoint API endpoint path
   * @param {object|null} body Request body (default: null)
   * @param {object} headers Additional HTTP headers (default: {})
   * @returns {Promise<Response>} Promise resolving to Response object
   *
   * @example
   * const response = await client.post('/v1/payments', {
   *   amount: 100000,
   *   currency: 'IDR'
   * });
   */
  async post(endpoint, body = null, headers = {}) {
    return this.requestWithRetry("POST", endpoint, body, headers);
  }

  /**
   * Send PUT request
   *
   * @param {string} endpoint API endpoint path
   * @param {object|null} body Request body (default: null)
   * @param {object} headers Additional HTTP headers (default: {})
   * @returns {Promise<Response>} Promise resolving to Response object
   *
   * @example
   * const response = await client.put('/v1/payments/123', {
   *   status: 'cancelled'
   * });
   */
  async put(endpoint, body = null, headers = {}) {
    return this.requestWithRetry("PUT", endpoint, body, headers);
  }

  /**
   * Send PATCH request
   *
   * @param {string} endpoint API endpoint path
   * @param {object|null} body Request body (default: null)
   * @param {object} headers Additional HTTP headers (default: {})
   * @returns {Promise<Response>} Promise resolving to Response object
   *
   * @example
   * const response = await client.patch('/v1/payments/123', {
   *   metadata: { note: 'Updated payment' }
   * });
   */
  async patch(endpoint, body = null, headers = {}) {
    return this.requestWithRetry("PATCH", endpoint, body, headers);
  }

  /**
   * Send DELETE request
   *
   * @param {string} endpoint API endpoint path
   * @param {object} headers Additional HTTP headers (default: {})
   * @returns {Promise<Response>} Promise resolving to Response object
   *
   * @example
   * const response = await client.delete('/v1/payments/123');
   * if (response.isSuccess()) {
   *   console.log('Payment deleted successfully');
   * }
   */
  async delete(endpoint, headers = {}) {
    return this.requestWithRetry("DELETE", endpoint, null, headers);
  }

  /**
   * Execute request with automatic retry logic
   *
   * Handles automatic retries for failed requests with exponential backoff.
   * Supports token refresh for authentication errors and retries for
   * transient network errors.
   *
   * @private
   * @param {string} method HTTP method
   * @param {string} endpoint API endpoint path
   * @param {object|null} body Request body
   * @param {object} headers HTTP headers
   * @returns {Promise<Response>} Promise resolving to Response object
   *
   * @throws {AuthenticationException} When authentication fails and cannot be recovered
   * @throws {ApiException} When request fails after all retry attempts
   */
  async requestWithRetry(method, endpoint, body = null, headers = {}) {
    let retryCount = 0;
    const maxRetries = this.config.getMaxRetries();
    let lastException = null;

    while (retryCount <= maxRetries) {
      try {
        return await this.request(method, endpoint, body, headers);
      } catch (error) {
        lastException = error;

        if (error instanceof AuthenticationException) {
          if (this.config.isAutoReauthEnabled() && retryCount < maxRetries) {
            retryCount++;

            if (!this.isRefreshingToken) {
              this.isRefreshingToken = true;
              try {
                await this.auth.refreshToken();
                await this.sleep(this.config.getRetryDelay());
                this.isRefreshingToken = false;
                continue;
              } catch (refreshError) {
                this.isRefreshingToken = false;
                break;
              }
            } else {
              await this.sleep(this.config.getRetryDelay());
              continue;
            }
          } else {
            break;
          }
        }

        if (this.shouldRetry(error, retryCount, maxRetries)) {
          retryCount++;
          await this.sleep(this.calculateRetryDelay(retryCount));
          continue;
        }

        break;
      }
    }

    const finalMessage = `Request failed after ${retryCount} attempt(s)`;
    throw lastException || new ApiException(finalMessage);
  }

  /**
   * Determine if a request should be retried
   *
   * @private
   * @param {Error} exception The exception that occurred
   * @param {number} retryCount Current retry attempt count
   * @param {number} maxRetries Maximum allowed retries
   * @returns {boolean} True if request should be retried
   */
  shouldRetry(exception, retryCount, maxRetries) {
    if (retryCount >= maxRetries) {
      return false;
    }

    const statusCode = exception.getCode ? exception.getCode() : exception.code;

    // Retryable HTTP status codes
    const retryableStatusCodes = [408, 429, 500, 502, 503, 504];
    return retryableStatusCodes.includes(statusCode);
  }

  /**
   * Check if error is a network error
   *
   * @private
   * @param {Error} error The error to check
   * @returns {boolean} True if error is network-related
   */
  isNetworkError(error) {
    // Check for network-related errors
    return (
      !error.response ||
      error.code === "ECONNABORTED" ||
      error.code === "ENOTFOUND" ||
      error.code === "ECONNRESET" ||
      error.code === "ECONNREFUSED"
    );
  }

  /**
   * Calculate retry delay with exponential backoff and jitter
   *
   * @private
   * @param {number} retryCount Current retry attempt count
   * @returns {number} Delay in milliseconds
   */
  calculateRetryDelay(retryCount) {
    // Exponential backoff with jitter
    const baseDelay = this.config.getRetryDelay();
    const exponentialDelay = baseDelay * Math.pow(2, retryCount - 1);
    const jitter = Math.random() * 0.3 * exponentialDelay;
    return Math.min(exponentialDelay + jitter, 30000);
  }

  /**
   * Execute HTTP request
   *
   * @private
   * @param {string} method HTTP method
   * @param {string} endpoint API endpoint path
   * @param {object|null} body Request body
   * @param {object} headers HTTP headers
   * @returns {Promise<Response>} Promise resolving to Response object
   *
   * @throws {AuthenticationException} For 401 responses
   * @throws {ValidationException} For 422 responses
   * @throws {ApiException} For other error responses
   */
  async request(method, endpoint, body = null, headers = {}) {
    const options = {
      method,
      url: endpoint,
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        ...this.config.getCustomHeaders(),
        ...headers,
      },
    };

    if (body !== null && body !== undefined) {
      options.data = body;
    }

    // Request interceptors
    for (const interceptor of this.interceptors) {
      if (interceptor.request) {
        await interceptor.request(method, endpoint, options);
      }
    }

    const startTime = Date.now();

    try {
      const response = await this.httpClient.request(options);
      const responseTime = Date.now() - startTime;

      const singaPayResponse = new Response(response.status, response.data);

      // Response interceptors
      for (const interceptor of this.interceptors) {
        if (interceptor.response) {
          await interceptor.response(
            method,
            endpoint,
            options,
            singaPayResponse,
            responseTime
          );
        }
      }

      return singaPayResponse;
    } catch (error) {
      const responseTime = Date.now() - startTime;

      // Error interceptors
      for (const interceptor of this.interceptors) {
        if (interceptor.error) {
          await interceptor.error(
            method,
            endpoint,
            options,
            error,
            responseTime
          );
        }
      }

      if (error.response) {
        const statusCode = error.response.status;
        const body = error.response.data || {};

        const singaPayResponse = new Response(statusCode, body);

        if (statusCode === 401) {
          throw new AuthenticationException(
            singaPayResponse.getMessage() || "Authentication failed",
            statusCode,
            error
          );
        } else if (statusCode === 422) {
          throw new ValidationException(
            singaPayResponse.getMessage() || "Validation failed",
            body.errors || [],
            statusCode,
            error
          );
        } else {
          throw new ApiException(
            singaPayResponse.getMessage() || "API request failed",
            statusCode,
            error
          );
        }
      } else {
        // Network error
        throw new ApiException(
          error.message || "HTTP request failed",
          0,
          error
        );
      }
    }
  }

  /**
   * Sleep for specified milliseconds
   *
   * @private
   * @param {number} ms Milliseconds to sleep
   * @returns {Promise} Promise that resolves after specified time
   */
  sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
  }

  /**
   * Get underlying HTTP client instance
   *
   * @returns {object} Axios HTTP client instance
   *
   * @example
   * const httpClient = client.getHttpClient();
   * httpClient.defaults.headers.common['X-Custom-Header'] = 'value';
   */
  getHttpClient() {
    return this.httpClient;
  }

  /**
   * Get request metrics from MetricsInterceptor
   *
   * @returns {object} Metrics data including request counts and response times
   *
   * @example
   * const metrics = client.getMetrics();
   * console.log(`Total requests: ${metrics.totalRequests}`);
   * console.log(`Average response time: ${metrics.averageResponseTime}ms`);
   */
  getMetrics() {
    for (const interceptor of this.interceptors) {
      if (interceptor instanceof MetricsInterceptor) {
        return interceptor.getMetrics();
      }
    }
    return {};
  }
}
