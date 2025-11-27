/**
 * Response - HTTP Response Wrapper Class
 *
 * A standardized wrapper for HTTP responses within the SingaPay SDK.
 * This class provides a consistent interface for handling API responses
 * with convenient methods for accessing response data, error information,
 * pagination, and status checking.
 *
 * The Response class normalizes different API response formats and provides
 * type-safe access to common response properties across all SingaPay API endpoints.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class Response {
  /**
   * Creates a new Response instance
   *
   * @param {number} statusCode HTTP status code (e.g., 200, 400, 500)
   * @param {object} body Response body object (default: {})
   *
   * @example
   * const response = new Response(200, {
   *   success: true,
   *   data: { id: 123, status: 'completed' },
   *   message: 'Operation successful'
   * });
   */
  constructor(statusCode, body = {}) {
    /**
     * @type {number}
     */
    this.statusCode = statusCode;
    /**
     * @type {object}
     */
    this.body = body;
  }

  /**
   * Check if the response indicates success
   *
   * Determines whether the response represents a successful operation.
   * This checks both the HTTP status code (2xx range) and the presence
   * of a success flag in the response body.
   *
   * @returns {boolean} True if both status code and success flag indicate success
   *
   * @example
   * if (response.isSuccess()) {
   *   console.log('Operation completed successfully');
   * } else {
   *   console.log('Operation failed');
   * }
   */
  isSuccess() {
    return (
      this.statusCode >= 200 &&
      this.statusCode < 300 &&
      (this.body.success ?? false) === true
    );
  }

  /**
   * Get the HTTP status code
   *
   * @returns {number} HTTP status code (e.g., 200, 400, 500)
   *
   * @example
   * const status = response.getStatusCode();
   * console.log(`HTTP Status: ${status}`);
   */
  getStatusCode() {
    return this.statusCode;
  }

  /**
   * Get the complete response body
   *
   * @returns {object} Complete response body object
   *
   * @example
   * const body = response.getBody();
   * console.log('Full response:', body);
   */
  getBody() {
    return this.body;
  }

  /**
   * Get the response data payload
   *
   * Returns the main data payload from the response. If the response
   * contains a `data` property, that is returned. Otherwise, the
   * entire response body is returned as fallback.
   *
   * @returns {*} Response data payload or entire body
   *
   * @example
   * const data = response.getData();
   * console.log('Response data:', data);
   */
  getData() {
    return this.body.data || this.body;
  }

  /**
   * Get error information from response
   *
   * Extracts error information from the response body. Returns null
   * if no error information is present.
   *
   * @returns {object|null} Error object or null if no error
   *
   * @example
   * const error = response.getError();
   * if (error) {
   *   console.error('API Error:', error);
   * }
   */
  getError() {
    return this.body.error || null;
  }

  /**
   * Get human-readable message from response
   *
   * Extracts a human-readable message from the response, prioritizing
   * error messages if present, then general messages, or null if no
   * message is available.
   *
   * @returns {string|null} Message string or null
   *
   * @example
   * const message = response.getMessage();
   * if (message) {
   *   console.log('Response message:', message);
   * }
   */
  getMessage() {
    if (this.body.error?.message) {
      return this.body.error.message;
    }

    if (this.body.message) {
      return this.body.message;
    }

    return null;
  }

  /**
   * Get error code or status code
   *
   * Returns the error code from the response body if available,
   * otherwise falls back to the HTTP status code.
   *
   * @returns {number} Error code or HTTP status code
   *
   * @example
   * const code = response.getCode();
   * console.log(`Error code: ${code}`);
   */
  getCode() {
    return this.body.error?.code || this.statusCode;
  }

  /**
   * Get pagination information
   *
   * Extracts pagination metadata from the response if available.
   * Returns null if no pagination information is present.
   *
   * @returns {object|null} Pagination object or null
   *
   * @example
   * const pagination = response.getPagination();
   * if (pagination) {
   *   console.log(`Page ${pagination.current_page} of ${pagination.total_pages}`);
   * }
   */
  getPagination() {
    return this.body.pagination || null;
  }

  /**
   * Convert response to plain object
   *
   * Returns the response body as a plain JavaScript object.
   * This is useful for serialization or when working with
   * libraries that expect plain objects.
   *
   * @returns {object} Response body as plain object
   *
   * @example
   * const obj = response.toObject();
   * const json = JSON.stringify(obj);
   */
  toObject() {
    return this.body;
  }
}
