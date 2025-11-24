<?php

namespace SingaPay\Exceptions;

/**
 * SingaPay Validation Exception
 * 
 * Specialized exception for handling request validation errors returned by
 * the SingaPay API. This exception captures both the high-level error message
 * and detailed field-level validation errors, providing comprehensive
 * validation feedback for partner applications.
 * 
 * Validation exceptions typically occur when API requests contain invalid,
 * missing, or malformed data that fails server-side validation rules. This
 * exception enables precise error reporting and facilitates user-friendly
 * validation error display in partner applications.
 * 
 * @package SingaPay\Exceptions
 * @author PT. Abadi Singapay Indonesia
  */
class ValidationException extends SingaPayException
{
    /**
     * @var array Detailed field-level validation errors
     * 
     * Structure typically follows:
     * [
     *     'field_name' => [
     *         'Error message for field_name',
     *         'Additional error message for field_name'
     *     ],
     *     'nested_field' => [
     *         'child_field' => [
     *             'Error message for nested field'
     *         ]
     *     ]
     * ]
     */
    private $errors;

    /**
     * Construct Validation Exception
     * 
     * Initializes a new validation exception with comprehensive error information
     * including both a general error message and detailed field-specific validation
     * errors. Supports exception chaining for complex error scenarios.
     * 
     * @param string $message General validation error message describing the overall validation failure
     * @param array $errors Detailed field-level validation errors in structured format
     * @param int $code HTTP status code or custom error code (typically 422 for validation errors)
     * @param \Throwable|null $previous Previous exception in the chain for nested exception scenarios
     * 
     * @example
     * // Basic validation exception
     * throw new ValidationException(
     *     'The given data was invalid',
     *     [
     *         'email' => ['The email field is required'],
     *         'phone' => ['The phone field must be numeric']
     *     ]
     * );
     * 
     * // Validation exception with HTTP status code
     * throw new ValidationException(
     *     'Request validation failed',
     *     $fieldErrors,
     *     422, // HTTP Unprocessable Entity
     *     $previousException
     * );
     */
    public function __construct($message = "", $errors = [], $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    /**
     * Retrieve detailed validation errors
     * 
     * Returns the complete collection of field-level validation errors
     * captured during the API request. The errors are structured to
     * facilitate precise error reporting and user interface integration.
     * 
     * @return array Associative array of validation errors where keys are
     *               field names and values are arrays of error messages
     * 
     * @example
     * try {
     *     $account = $singapay->account->create($data);
     * } catch (ValidationException $e) {
     *     $errors = $e->getErrors();
     *     
     *     foreach ($errors as $field => $messages) {
     *         echo "Field '{$field}': " . implode(', ', $messages) . "\n";
     *     }
     * }
     * 
     * // Example output:
     * // Field 'email': The email field is required, The email must be valid
     * // Field 'phone': The phone field must be numeric
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
