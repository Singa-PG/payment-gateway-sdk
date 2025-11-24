<?php

namespace SingaPay\Http;

/**
 * SingaPay HTTP Response Handler
 * 
 * Provides a standardized interface for handling and parsing HTTP responses
 * from SingaPay API endpoints. This class encapsulates response data and
 * provides convenient methods for accessing different parts of the response
 * with proper error handling and data extraction.
 * 
 * @package SingaPay\Http
 * @author PT. Abadi Singapay Indonesia
  */
class Response
{
    /**
     * @var int HTTP status code
     */
    private $statusCode;

    /**
     * @var array Decoded response body
     */
    private $body;

    /**
     * Initialize Response instance
     * 
     * Creates a new response instance with the provided HTTP status code
     * and parsed response body. The response body should be pre-decoded
     * from JSON format into an associative array.
     * 
     * @param int $statusCode HTTP status code (e.g., 200, 400, 500)
     * @param array $body Decoded response body as associative array
     * 
     * @example
     * $response = new Response(200, [
     *     'success' => true,
     *     'data' => ['id' => 'acc_123', 'name' => 'Test Account'],
     *     'message' => 'Account created successfully'
     * ]);
     */
    public function __construct($statusCode, array $body = [])
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
    }

    /**
     * Check if response indicates successful operation
     * 
     * Determines whether the API request was successful based on both
     * HTTP status code and the presence of a 'success' flag in the response body.
     * Successful responses have HTTP status codes in the 200-299 range
     * and contain 'success' => true in the response body.
     * 
     * @return bool True if response indicates success, false otherwise
     * 
     * @example
     * if ($response->isSuccess()) {
     *     // Process successful response
     *     $data = $response->getData();
     * } else {
     *     // Handle error response
     *     $errorMessage = $response->getMessage();
     * }
     */
    public function isSuccess()
    {
        return $this->statusCode >= 200 && $this->statusCode < 300
            && ($this->body['success'] ?? false) === true;
    }

    /**
     * Get HTTP status code
     * 
     * Returns the HTTP status code from the API response. This can be used
     * for custom error handling or logging based on specific status codes.
     * 
     * @return int HTTP status code (e.g., 200, 201, 400, 401, 500)
     * 
     * @example
     * $statusCode = $response->getStatusCode();
     * 
     * if ($statusCode === 401) {
     *     // Handle unauthorized access
     *     $this->refreshAuthentication();
     * }
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Get complete response body
     * 
     * Returns the full decoded response body as an associative array.
     * This includes all data, metadata, and structural elements from the API response.
     * 
     * @return array Complete response body
     * 
     * @example
     * $fullResponse = $response->getBody();
     * 
     * // Access custom fields not covered by other methods
     * $customField = $fullResponse['custom_field'] ?? null;
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Extract primary data from response
     * 
     * Returns the main data payload from the API response. This typically
     * contains the requested resource data such as account information,
     * payment details, or transaction records.
     * 
     * @return mixed Primary data payload or null if not present
     * 
     * @example
     * // Get account data from response
     * $accountData = $response->getData();
     * 
     * if ($accountData) {
     *     $accountId = $accountData['id'];
     *     $accountName = $accountData['name'];
     * }
     */
    public function getData()
    {
        return $this->body['data'] ?? null;
    }

    /**
     * Get error information
     * 
     * Returns detailed error information from the response when the request
     * was not successful. This typically includes error codes, messages,
     * and additional context about what went wrong.
     * 
     * @return array|null Error details or null if no error information
     * 
     * @example
     * $errorInfo = $response->getError();
     * 
     * if ($errorInfo) {
     *     $errorCode = $errorInfo['code'];
     *     $errorMessage = $errorInfo['message'];
     *     $errorDetails = $errorInfo['details'] ?? [];
     * }
     */
    public function getError()
    {
        return $this->body['error'] ?? null;
    }

    /**
     * Extract error or success message
     * 
     * Returns a human-readable message from the response. For successful
     * responses, this is typically a confirmation message. For errors,
     * this provides a description of what went wrong.
     * 
     * @return string|null Message text or null if no message present
     * 
     * @example
     * $message = $response->getMessage();
     * 
     * if ($response->isSuccess()) {
     *     $this->logSuccess($message);
     * } else {
     *     $this->logError("API Error: " . $message);
     * }
     */
    public function getMessage()
    {
        if (isset($this->body['error']['message'])) {
            return $this->body['error']['message'];
        }

        if (isset($this->body['message'])) {
            return $this->body['message'];
        }

        return null;
    }

    /**
     * Get error or status code
     * 
     * Returns an error code from the response body if available, otherwise
     * falls back to the HTTP status code. This provides a consistent way
     * to identify specific error conditions regardless of how they're represented.
     * 
     * @return int Error code or HTTP status code
     * 
     * @example
     * $errorCode = $response->getCode();
     * 
     * switch ($errorCode) {
     *     case 'VALIDATION_ERROR':
     *         $this->handleValidationError();
     *         break;
     *     case 'INSUFFICIENT_FUNDS':
     *         $this->handleInsufficientFunds();
     *         break;
     * }
     */
    public function getCode()
    {
        return $this->body['error']['code'] ?? $this->statusCode;
    }

    /**
     * Get pagination information
     * 
     * Returns pagination metadata for list responses. This includes information
     * about total items, current page, items per page, and navigation links
     * for efficient data retrieval across multiple pages.
     * 
     * @return array|null Pagination data or null if not applicable
     * 
     * @example
     * $pagination = $response->getPagination();
     * 
     * if ($pagination) {
     *     $currentPage = $pagination['current_page'];
     *     $totalPages = $pagination['total_pages'];
     *     $totalItems = $pagination['total_items'];
     *     $hasNextPage = $pagination['has_next'];
     * }
     */
    public function getPagination()
    {
        return $this->body['pagination'] ?? null;
    }

    /**
     * Convert response to array representation
     * 
     * Returns the complete response data as an associative array. This is
     * useful for serialization, logging, or when the full response needs
     * to be passed to other parts of the application.
     * 
     * @return array Complete response data
     * 
     * @example
     * // Log complete response for debugging
     * $responseArray = $response->toArray();
     * \Log::debug('API Response', $responseArray);
     * 
     * // Pass response to view or API output
     * return response()->json($response->toArray());
     */
    public function toArray()
    {
        return $this->body;
    }
}
