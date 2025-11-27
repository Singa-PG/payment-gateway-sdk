/**
 * InterceptorInterface - Base Interface for HTTP Interceptors
 *
 * Defines the standard contract for HTTP request/response interceptors used
 * throughout the SingaPay SDK. Interceptors allow for monitoring, modifying,
 * and tracking HTTP communications between the client and SingaPay APIs.
 *
 * Implementations of this interface can be used for logging, metrics collection,
 * request transformation, response processing, and error handling.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class InterceptorInterface {
  /**
   * Handle outgoing HTTP request
   *
   * Called before an HTTP request is sent. Can be used to modify request options,
   * add headers, log requests, or track metrics.
   *
   * @param {string} _method HTTP method (GET, POST, PUT, etc.)
   * @param {string} _endpoint API endpoint path
   * @param {object} _options Request options including headers and data
   * @returns {Promise<void>}
   *
   * @example
   * async request(_method, _endpoint, _options) {
   *   console.log(`Sending ${_method} request to ${_endpoint}`);
   *   _options.headers['X-Request-ID'] = generateRequestId();
   * }
   */
  async request(_method, _endpoint, _options) {}

  /**
   * Handle successful HTTP response
   *
   * Called after a successful HTTP response is received. Can be used to
   * process responses, log success, collect metrics, or transform response data.
   *
   * @param {string} _method HTTP method used for the request
   * @param {string} _endpoint API endpoint path
   * @param {object} _options Request options that were sent
   * @param {Response} _response Response object from the API
   * @param {number} _responseTime Response time in milliseconds
   * @returns {Promise<void>}
   *
   * @example
   * async response(_method, _endpoint, _options, _response, _responseTime) {
   *   console.log(`Request completed in ${_responseTime}ms with status ${_response.getStatusCode()}`);
   * }
   */
  async response(_method, _endpoint, _options, _response, _responseTime) {}

  /**
   * Handle HTTP error
   *
   * Called when an HTTP request fails. Can be used for error logging,
   * monitoring, alerting, or custom error handling logic.
   *
   * @param {string} _method HTTP method used for the request
   * @param {string} _endpoint API endpoint path
   * @param {object} _options Request options that were sent
   * @param {Error} _error Error that occurred
   * @param {number} _responseTime Response time in milliseconds (if available)
   * @returns {Promise<void>}
   *
   * @example
   * async error(_method, _endpoint, _options, _error, _responseTime) {
   *   console.error(`Request failed after ${_responseTime}ms:`, _error.message);
   *   await sendErrorAlert(_error, _method, _endpoint);
   * }
   */
  async error(_method, _endpoint, _options, _error, _responseTime) {}
}
