/**
 * SingaPayException - Base Exception Class
 *
 * The root exception class for all SingaPay SDK exceptions. Provides a consistent
 * structure for error handling throughout the SDK with support for error codes,
 * original error preservation, and proper stack trace capture.
 *
 * This exception class extends the native JavaScript Error class and adds
 * additional properties specific to SingaPay error handling requirements.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class SingaPayException extends Error {
  /**
   * Creates a new SingaPayException instance
   *
   * @param {string} message Human-readable error description
   * @param {number} code Numeric error code for programmatic identification (default: 0)
   * @param {Error|null} originalError Original error that caused this exception (default: null)
   *
   * @example
   * throw new SingaPayException('Payment processing failed', 1001, originalError);
   */
  constructor(message, code = 0, originalError = null) {
    super(message);
    this.name = "SingaPayException";
    this.code = code;
    this.originalError = originalError;
    Error.captureStackTrace(this, this.constructor);
  }
}

/**
 * ApiException - API Communication Exception
 *
 * Represents errors that occur during API communication with SingaPay servers.
 * This includes HTTP errors, network timeouts, and invalid API responses.
 *
 * @extends SingaPayException
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class ApiException extends SingaPayException {
  /**
   * Creates a new ApiException instance
   *
   * @param {string} message Human-readable error description
   * @param {number} code HTTP status code or API-specific error code (default: 0)
   * @param {Error|null} originalError Original error that caused this exception (default: null)
   *
   * @example
   * throw new ApiException('API request timeout', 408, timeoutError);
   */
  constructor(message, code = 0, originalError = null) {
    super(message, code, originalError);
    this.name = "ApiException";
  }
}

/**
 * AuthenticationException - Authentication & Authorization Exception
 *
 * Represents errors related to authentication and authorization failures.
 * This includes invalid API keys, expired tokens, insufficient permissions,
 * and other security-related errors.
 *
 * @extends SingaPayException
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class AuthenticationException extends SingaPayException {
  /**
   * Creates a new AuthenticationException instance
   *
   * @param {string} message Human-readable error description
   * @param {number} code Authentication-specific error code (default: 0)
   * @param {Error|null} originalError Original error that caused this exception (default: null)
   *
   * @example
   * throw new AuthenticationException('Invalid API key provided', 401, authError);
   */
  constructor(message, code = 0, originalError = null) {
    super(message, code, originalError);
    this.name = "AuthenticationException";
  }
}

/**
 * ValidationException - Data Validation Exception
 *
 * Represents errors related to data validation failures. This exception
 * includes detailed error information for multiple fields, making it
 * suitable for form validation and data processing scenarios.
 *
 * @extends SingaPayException
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class ValidationException extends SingaPayException {
  /**
   * Creates a new ValidationException instance
   *
   * @param {string} message Human-readable error description
   * @param {object} errors Object containing field-specific validation errors (default: {})
   * @param {number} code Validation-specific error code (default: 0)
   * @param {Error|null} originalError Original error that caused this exception (default: null)
   *
   * @example
   * throw new ValidationException(
   *   'Invalid input data',
   *   { email: 'Must be valid email', amount: 'Must be positive number' },
   *   422,
   *   validationError
   * );
   */
  constructor(message, errors = {}, code = 0, originalError = null) {
    super(message, code, originalError);
    this.name = "ValidationException";
    /**
     * @type {object}
     */
    this.errors = errors;
  }

  /**
   * Get validation errors
   *
   * Returns the detailed field-specific validation errors associated with
   * this exception. The returned object contains field names as keys and
   * error messages as values.
   *
   * @returns {object} Object containing field-specific validation errors
   *
   * @example
   * try {
   *   await client.processPayment(payload);
   * } catch (error) {
   *   if (error instanceof ValidationException) {
   *     const fieldErrors = error.getErrors();
   *     console.log('Validation errors:', fieldErrors);
   *   }
   * }
   */
  getErrors() {
    return this.errors;
  }
}
