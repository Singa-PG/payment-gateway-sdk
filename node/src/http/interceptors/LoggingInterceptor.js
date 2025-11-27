import { InterceptorInterface } from "./InterceptorInterface.js";

/**
 * LoggingInterceptor - HTTP Request/Response Logger
 *
 * An interceptor implementation that logs HTTP requests, responses, and errors
 * to help with debugging, monitoring, and audit trails. This interceptor
 * provides structured logging for all API communications with SingaPay.
 *
 * The logger can be customized to use any logging library that supports
 * info() and error() methods, with console being the default.
 *
 * @extends InterceptorInterface
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class LoggingInterceptor extends InterceptorInterface {
  /**
   * Creates a new LoggingInterceptor instance
   *
   * @param {object} logger Logger instance with info() and error() methods (default: console)
   *
   * @example
   * // Use default console logger
   * const interceptor = new LoggingInterceptor();
   *
   * // Use custom logger (Winston, Pino, etc.)
   * const interceptor = new LoggingInterceptor(myCustomLogger);
   */
  constructor(logger = console) {
    super();
    /**
     * @private
     * @type {object}
     */
    this.logger = logger;
  }

  /**
   * Log outgoing HTTP requests
   *
   * Logs request details including method, endpoint, headers, and body presence
   * before the request is sent to SingaPay APIs.
   *
   * @param {string} method HTTP method (GET, POST, PUT, etc.)
   * @param {string} endpoint API endpoint path
   * @param {object} options Request options including headers and data
   * @returns {Promise<void>}
   *
   * @example
   * // Logs: SingaPay API Request { method: 'POST', endpoint: '/v1/payments', headers: ['Accept', 'Content-Type'], hasBody: true }
   */
  async request(method, endpoint, options) {
    if (this.logger) {
      this.logger.info("SingaPay API Request", {
        method,
        endpoint,
        headers: Object.keys(options.headers || {}),
        hasBody: !!options.data,
      });
    }
  }

  /**
   * Log successful HTTP responses
   *
   * Logs response details including method, endpoint, status code, success status,
   * and response time after a successful response is received from SingaPay APIs.
   *
   * @param {string} method HTTP method used for the request
   * @param {string} endpoint API endpoint path
   * @param {object} options Request options that were sent
   * @param {Response} response Response object from the API
   * @param {number} responseTime Response time in milliseconds
   * @returns {Promise<void>}
   *
   * @example
   * // Logs: SingaPay API Response { method: 'POST', endpoint: '/v1/payments', statusCode: 200, success: true, responseTime: '245.67ms' }
   */
  async response(method, endpoint, options, response, responseTime) {
    if (this.logger) {
      this.logger.info("SingaPay API Response", {
        method,
        endpoint,
        statusCode: response.getStatusCode(),
        success: response.isSuccess(),
        responseTime: `${responseTime.toFixed(2)}ms`,
      });
    }
  }

  /**
   * Log HTTP errors
   *
   * Logs error details including method, endpoint, error message, error code,
   * and response time when an HTTP request fails or returns an error response.
   *
   * @param {string} method HTTP method used for the request
   * @param {string} endpoint API endpoint path
   * @param {object} options Request options that were sent
   * @param {Error} error Error that occurred
   * @param {number} responseTime Response time in milliseconds
   * @returns {Promise<void>}
   *
   * @example
   * // Logs: SingaPay API Error { method: 'POST', endpoint: '/v1/payments', error: 'Authentication failed', code: 401, responseTime: '120.45ms' }
   */
  async error(method, endpoint, options, error, responseTime) {
    if (this.logger) {
      this.logger.error("SingaPay API Error", {
        method,
        endpoint,
        error: error.message,
        code: error.code,
        responseTime: `${responseTime.toFixed(2)}ms`,
      });
    }
  }
}
