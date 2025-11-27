import { InterceptorInterface } from "./InterceptorInterface.js";

/**
 * MetricsInterceptor - HTTP Metrics Collector
 *
 * An interceptor implementation that collects performance metrics and statistics
 * for all HTTP requests made through the SingaPay SDK. This interceptor tracks
 * request counts, success/failure rates, response times, and other valuable
 * performance indicators.
 *
 * The collected metrics can be used for monitoring, alerting, performance
 * optimization, and capacity planning of SingaPay API integrations.
 *
 * @extends InterceptorInterface
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class MetricsInterceptor extends InterceptorInterface {
  /**
   * Creates a new MetricsInterceptor instance
   *
   * Initializes the metrics storage with zero values for all counters and
   * null for timestamp fields.
   *
   * @example
   * const interceptor = new MetricsInterceptor();
   * client.addInterceptor(interceptor);
   */
  constructor() {
    super();
    /**
     * @private
     * @type {object}
     */
    this.metrics = {
      totalRequests: 0,
      successfulRequests: 0,
      failedRequests: 0,
      totalResponseTime: 0,
      lastRequestTime: null,
    };
  }

  /**
   * Track outgoing HTTP request
   *
   * Increments the total request counter and records the timestamp when
   * a request is initiated to SingaPay APIs.
   *
   * @param {string} _method HTTP method (GET, POST, PUT, etc.)
   * @param {string} _endpoint API endpoint path
   * @param {object} _options Request options including headers and data
   * @returns {Promise<void>}
   */
  async request(_method, _endpoint, _options) {
    this.metrics.totalRequests++;
    this.metrics.lastRequestTime = Date.now();
  }

  /**
   * Track successful HTTP response
   *
   * Increments the successful request counter and accumulates response time
   * when a successful response is received from SingaPay APIs.
   *
   * @param {string} _method HTTP method used for the request
   * @param {string} _endpoint API endpoint path
   * @param {object} _options Request options that were sent
   * @param {Response} _response Response object from the API
   * @param {number} responseTime Response time in milliseconds
   * @returns {Promise<void>}
   */
  async response(_method, _endpoint, _options, _response, responseTime) {
    this.metrics.successfulRequests++;
    this.metrics.totalResponseTime += responseTime;
  }

  /**
   * Track HTTP error
   *
   * Increments the failed request counter and accumulates response time
   * when an HTTP request fails or returns an error response.
   *
   * @param {string} _method HTTP method used for the request
   * @param {string} _endpoint API endpoint path
   * @param {object} _options Request options that were sent
   * @param {Error} _error Error that occurred
   * @param {number} responseTime Response time in milliseconds
   * @returns {Promise<void>}
   */
  async error(_method, _endpoint, _options, _error, responseTime) {
    this.metrics.failedRequests++;
    this.metrics.totalResponseTime += responseTime;
  }

  /**
   * Get complete metrics snapshot
   *
   * Returns a copy of all collected metrics including calculated values
   * like average response time. The returned object is a snapshot and
   * will not reflect subsequent metric changes.
   *
   * @returns {object} Metrics snapshot object with properties:
   * @returns {number} returns.totalRequests Total number of requests made
   * @returns {number} returns.successfulRequests Number of successful requests (2xx status)
   * @returns {number} returns.failedRequests Number of failed requests (non-2xx status)
   * @returns {number} returns.totalResponseTime Cumulative response time in milliseconds
   * @returns {number|null} returns.lastRequestTime Timestamp of last request initiation
   * @returns {number} returns.averageResponseTime Average response time in milliseconds
   * @returns {number} returns.successRate Success rate as percentage (0-100)
   *
   * @example
   * const metrics = interceptor.getMetrics();
   * console.log(`Success rate: ${metrics.successRate.toFixed(1)}%`);
   * console.log(`Average response time: ${metrics.averageResponseTime.toFixed(2)}ms`);
   */
  getMetrics() {
    const averageResponseTime = this.getAverageResponseTime();
    const successRate =
      this.metrics.totalRequests > 0
        ? (this.metrics.successfulRequests / this.metrics.totalRequests) * 100
        : 0;

    return {
      ...this.metrics,
      averageResponseTime,
      successRate,
    };
  }

  /**
   * Calculate average response time
   *
   * Computes the average response time across all completed requests
   * (both successful and failed). Returns 0 if no requests have been made.
   *
   * @returns {number} Average response time in milliseconds
   *
   * @example
   * const avgTime = interceptor.getAverageResponseTime();
   * console.log(`Average API response time: ${avgTime.toFixed(2)}ms`);
   */
  getAverageResponseTime() {
    if (this.metrics.totalRequests === 0) {
      return 0;
    }
    return this.metrics.totalResponseTime / this.metrics.totalRequests;
  }
}
