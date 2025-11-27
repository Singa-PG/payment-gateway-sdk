<?php

namespace SingaPay\Resources;

use SingaPay\Http\Client;
use SingaPay\Security\Authentication;
use SingaPay\Exceptions\ApiException;
use SingaPay\Exceptions\ValidationException;

/**
 * Payment Link Management Resource
 * 
 * Provides comprehensive payment link management capabilities for the SingaPay B2B Payment Gateway API.
 * This resource allows merchants to create, retrieve, update, and manage payment links for their accounts.
 * Payment links enable easy sharing of payment requests with customers via URLs that can be used
 * for various payment methods including virtual accounts, QRIS, and retail outlets.
 * 
 * @package SingaPay\Resources
 * @author PT. Abadi Singapay Indonesia
 */
class PaymentLink
{
    /**
     * @var Client HTTP client for API communication
     */
    private $client;

    /**
     * @var Authentication Authentication handler for token management
     */
    private $auth;

    /**
     * @var string API key for partner identification
     */
    private $apiKey;

    /**
     * Initialize Payment Link Resource
     * 
     * Constructs a new Payment Link resource instance with the provided HTTP client,
     * authentication handler, and API key for partner identification.
     * 
     * @param Client $client HTTP client for API communication
     * @param Authentication $auth Authentication handler for token management
     * @param string $apiKey Partner API key for request authentication
     */
    public function __construct(Client $client, Authentication $auth, $apiKey)
    {
        $this->client = $client;
        $this->auth = $auth;
        $this->apiKey = $apiKey;
    }

    /**
     * List Payment Links
     * 
     * Retrieves a paginated list of all payment links for a specific account.
     * Returns payment link details including reference number, title, status, amount, and usage statistics.
     * 
     * @param string $accountId Unique identifier of the account
     * @param int $page Page number for pagination (default: 1)
     * @param int $perPage Number of items per page (default: 25, max: 100)
     * 
     * @return array Response containing payment links list and pagination info
     * 
     * @throws ApiException When the API request fails
     * @throws AuthenticationException When authentication is invalid
     */
    public function list($accountId, $page = 1, $perPage = 25)
    {
        $headers = $this->getHeaders();
        $response = $this->client->get(
            "/api/v1.0/payment-link-manage/{$accountId}?page={$page}&per_page={$perPage}",
            $headers
        );

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Get Payment Link Details
     * 
     * Retrieves detailed information for a specific payment link by its unique identifier.
     * Returns complete payment link configuration including items, payment methods, and status.
     * 
     * @param string $accountId Unique identifier of the account
     * @param string $paymentLinkId Unique identifier of the payment link to retrieve
     * 
     * @return array Response containing payment link details
     * 
     * @throws ApiException When the API request fails or payment link not found
     * @throws AuthenticationException When authentication is invalid
     */
    public function get($accountId, $paymentLinkId)
    {
        $headers = $this->getHeaders();
        $response = $this->client->get(
            "/api/v1.0/payment-link-manage/{$accountId}/{$paymentLinkId}",
            $headers
        );

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Create Payment Link
     * 
     * Creates a new payment link for the specified account. The payment link can be shared
     * with customers to collect payments via various payment methods.
     * 
     * @param string $accountId Unique identifier of the account
     * @param array $data Payment link creation data containing:
     *                    - reff_no (string, required): Unique reference number for the payment link
     *                    - title (string, required): Title/description of the payment link
     *                    - total_amount (float, required): Total amount to be paid
     *                    - items (array, required): Array of item objects containing:
     *                         - name (string, required): Item name
     *                         - quantity (int, required): Item quantity
     *                         - unit_price (float, required): Item unit price
     *                    - required_customer_detail (bool, optional): Whether customer details are required
     *                    - max_usage (int, optional): Maximum number of times the link can be used
     *                    - expired_at (int, optional): Expiration timestamp in milliseconds
     *                    - whitelisted_payment_method (array, optional): Allowed payment methods
     * 
     * @return array Response containing created payment link details
     * 
     * @throws ValidationException When input data validation fails
     * @throws ApiException When the API request fails
     * @throws AuthenticationException When authentication is invalid
     */
    public function create($accountId, array $data)
    {
        $this->validateCreateData($data);

        $headers = $this->getHeaders();
        $response = $this->client->post(
            "/api/v1.0/payment-link-manage/{$accountId}",
            $data,
            $headers
        );

        if (!$response->isSuccess()) {
            $error = $response->getError();
            if (isset($error['errors'])) {
                throw new ValidationException($response->getMessage(), $error['errors'], $response->getCode());
            }
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Update Payment Link
     * 
     * Updates an existing payment link configuration. Can modify settings like status,
     * expiration, usage limits, and allowed payment methods.
     * 
     * @param string $accountId Unique identifier of the account
     * @param string $paymentLinkId Unique identifier of the payment link to update
     * @param array $data Payment link update data containing:
     *                    - required_customer_detail (bool, optional): Whether customer details are required
     *                    - max_usage (int, optional): Maximum number of times the link can be used
     *                    - expired_at (int, optional): Expiration timestamp in milliseconds
     *                    - status (string, optional): Payment link status ("open" or "closed")
     *                    - whitelisted_payment_method (array, optional): Allowed payment methods
     * 
     * @return array Response containing updated payment link details
     * 
     * @throws ApiException When the API request fails or payment link not found
     * @throws AuthenticationException When authentication is invalid
     */
    public function update($accountId, $paymentLinkId, array $data)
    {
        $headers = $this->getHeaders();
        $response = $this->client->put(
            "/api/v1.0/payment-link-manage/{$accountId}/{$paymentLinkId}",
            $data,
            $headers
        );

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Delete Payment Link
     * 
     * Permanently deletes a payment link. This action is irreversible and will
     * remove the payment link URL, making it inaccessible for future payments.
     * 
     * @param string $accountId Unique identifier of the account
     * @param string $paymentLinkId Unique identifier of the payment link to delete
     * 
     * @return array Response containing deletion confirmation
     * 
     * @throws ApiException When the API request fails or payment link not found
     * @throws AuthenticationException When authentication is invalid
     */
    public function delete($accountId, $paymentLinkId)
    {
        $headers = $this->getHeaders();
        $response = $this->client->delete(
            "/api/v1.0/payment-link-manage/{$accountId}/{$paymentLinkId}",
            $headers
        );

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Get Available Payment Methods
     * 
     * Retrieves the list of available payment methods that can be used for payment links.
     * Returns payment method details including code, name, group, and description.
     * 
     * @return array Response containing available payment methods
     * 
     * @throws ApiException When the API request fails
     * @throws AuthenticationException When authentication is invalid
     */
    public function getAvailablePaymentMethods()
    {
        $headers = $this->getHeaders();
        $response = $this->client->get("/api/v1.0/payment-link-manage/payment-methods", $headers);

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Validate Payment Link Creation Data
     * 
     * Performs client-side validation of payment link creation data before sending to the API.
     * Ensures all required fields are present and properly formatted according to API requirements.
     * 
     * @param array $data Payment link creation data to validate
     * 
     * @throws ValidationException When required fields are missing or invalid
     */
    private function validateCreateData(array $data)
    {
        $required = ['reff_no', 'title', 'total_amount', 'items'];
        $errors = [];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = "The {$field} field is required";
            }
        }

        if (isset($data['items']) && !is_array($data['items'])) {
            $errors['items'] = "The items field must be an array";
        }

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $index => $item) {
                if (!isset($item['name']) || empty($item['name'])) {
                    $errors["items.{$index}.name"] = "Item name is required";
                }
                if (!isset($item['quantity']) || !is_numeric($item['quantity'])) {
                    $errors["items.{$index}.quantity"] = "Item quantity must be numeric";
                }
                if (!isset($item['unit_price']) || !is_numeric($item['unit_price'])) {
                    $errors["items.{$index}.unit_price"] = "Item unit price must be numeric";
                }
            }
        }

        if (isset($data['total_amount']) && !is_numeric($data['total_amount'])) {
            $errors['total_amount'] = "Total amount must be numeric";
        }

        if (isset($data['max_usage']) && (!is_int($data['max_usage']) || $data['max_usage'] < 1)) {
            $errors['max_usage'] = "Max usage must be a positive integer";
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }

    /**
     * Get Standard Request Headers
     * 
     * Constructs the standard HTTP headers required for all API requests, including
     * authentication, content type, and partner identification headers.
     * 
     * @return array Array of HTTP headers for API requests
     */
    private function getHeaders()
    {
        return [
            'X-PARTNER-ID' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->auth->getAccessToken(),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
    }
}
