<?php

namespace SingaPay\Exceptions;

/**
 * SingaPay Base Exception Class
 * 
 * Foundation exception class for all SingaPay SDK-specific exceptions.
 * Extends the native PHP Exception class to provide a standardized base
 * for all SDK exception types while maintaining full compatibility with
 * PHP's built-in exception handling mechanisms.
 * 
 * This class serves as the root of the SingaPay exception hierarchy,
 * enabling consistent exception handling patterns across the entire SDK
 * and facilitating precise error categorization for partner applications.
 * 
 * @package SingaPay\Exceptions
 * @author PT. Abadi Singapay Indonesia
  */
class SingaPayException extends \Exception
{
    /**
     * Construct SingaPay Exception
     * 
     * Initializes a new SingaPay exception instance with the specified
     * error message, error code, and optional previous exception for
     * exception chaining. Maintains full compatibility with the native
     * PHP Exception constructor signature while providing a standardized
     * base for SDK-specific exception types.
     * 
     * @param string $message Human-readable error description detailing the exception context
     * @param int $code Numeric error code for programmatic error identification and handling
     * @param \Throwable|null $previous Previous exception in the chain for nested exception scenarios
     * 
     * @example
     * // Basic exception with message
     * throw new SingaPayException('API request timeout exceeded');
     * 
     * // Exception with custom error code
     * throw new SingaPayException('Invalid configuration', 1001);
     * 
     * // Exception with chained previous exception
     * try {
     *     // Some operation that may fail
     * } catch (\Exception $e) {
     *     throw new SingaPayException('Operation failed', 0, $e);
     * }
     */
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
