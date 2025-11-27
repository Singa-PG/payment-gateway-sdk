<?php

namespace SingaPay\Resources;

use SingaPay\Http\Client;
use SingaPay\Security\Authentication;
use SingaPay\Exceptions\ApiException;
use SingaPay\Exceptions\ValidationException;

/**
 * Account Management Resource
 * 
 * Provides comprehensive account management capabilities for the SingaPay B2B Payment Gateway API.
 * This resource allows merchants to create, retrieve, update, and manage sub-accounts under their
 * main merchant account. Each account represents a distinct entity that can have its own payment
 * links, virtual accounts, and transaction history.
 * 
 * @package SingaPay\Resources
 * @author PT. Abadi Singapay Indonesia
 */
class Account
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
     * Initialize Account Resource
     * 
     * Constructs a new Account resource instance with the provided HTTP client,
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
     * List All Accounts
     * 
     * Retrieves a paginated list of all sub-accounts under the merchant's main account.
     * Returns account details including ID, name, email, phone, and status for each account.
     * 
     * @param int $page Page number for pagination (default: 1)
     * @param int $perPage Number of items per page (default: 25, max: 100)
     * 
     * @return array Response containing accounts list and pagination info
     * 
     * @throws ApiException When the API request fails
     * @throws AuthenticationException When authentication is invalid
     */
    public function list($page = 1, $perPage = 25)
    {
        $headers = $this->getHeaders();
        $response = $this->client->get("/api/v1.0/accounts?page={$page}&per_page={$perPage}", $headers);

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Get Account Details
     * 
     * Retrieves detailed information for a specific account by its unique identifier.
     * Returns complete account profile including contact information and status.
     * 
     * @param string $accountId Unique identifier of the account to retrieve
     * 
     * @return array Response containing account details
     * 
     * @throws ApiException When the API request fails or account not found
     * @throws AuthenticationException When authentication is invalid
     */
    public function get($accountId)
    {
        $headers = $this->getHeaders();
        $response = $this->client->get("/api/v1.0/accounts/{$accountId}", $headers);

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Create New Account
     * 
     * Creates a new sub-account under the merchant's main account. The new account
     * can be used to manage payment links, virtual accounts, and transactions independently.
     * 
     * @param array $data Account creation data containing:
     *                    - name (string, required): Account name, max 100 characters
     *                    - phone (string, required): Phone number, 9-15 digits
     *                    - email (string, required): Valid email address, max 100 characters
     * 
     * @return array Response containing created account details
     * 
     * @throws ValidationException When input data validation fails
     * @throws ApiException When the API request fails
     * @throws AuthenticationException When authentication is invalid
     */
    public function create(array $data)
    {
        $this->validateCreateData($data);

        $headers = $this->getHeaders();
        $response = $this->client->post("/api/v1.0/accounts", $data, $headers);

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
     * Update Account Status
     * 
     * Updates the status of an existing account. Can be used to activate or deactivate
     * an account without deleting it. Deactivated accounts cannot perform transactions.
     * 
     * @param string $accountId Unique identifier of the account to update
     * @param string $status New status for the account ("active" or "inactive")
     * 
     * @return array Response containing updated account details
     * 
     * @throws ValidationException When status value is invalid
     * @throws ApiException When the API request fails or account not found
     * @throws AuthenticationException When authentication is invalid
     */
    public function updateStatus($accountId, $status)
    {
        if (!in_array($status, ['active', 'inactive'])) {
            throw new ValidationException('Status must be either "active" or "inactive"');
        }

        $headers = $this->getHeaders();
        $body = ['status' => $status];

        $response = $this->client->patch("/api/v1.0/accounts/update-status/{$accountId}", $body, $headers);

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Delete Account
     * 
     * Permanently deletes an account and all associated data. This action is irreversible
     * and will remove all payment links, virtual accounts, and transaction history for the account.
     * 
     * @param string $accountId Unique identifier of the account to delete
     * 
     * @return array Response containing deletion confirmation
     * 
     * @throws ApiException When the API request fails or account not found
     * @throws AuthenticationException When authentication is invalid
     */
    public function delete($accountId)
    {
        $headers = $this->getHeaders();
        $response = $this->client->delete("/api/v1.0/accounts/{$accountId}", $headers);

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Validate Account Creation Data
     * 
     * Performs client-side validation of account creation data before sending to the API.
     * Ensures all required fields are present and properly formatted according to API requirements.
     * 
     * @param array $data Account creation data to validate
     * 
     * @throws ValidationException When required fields are missing or invalid
     */
    private function validateCreateData(array $data)
    {
        $required = ['name', 'phone', 'email'];
        $errors = [];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = "The {$field} field is required";
            }
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }

        if (isset($data['phone']) && !preg_match('/^\d{9,15}$/', $data['phone'])) {
            $errors['phone'] = "Phone must be 9-15 digits";
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Email must be a valid email address";
        }

        if (isset($data['email']) && strlen($data['email']) > 100) {
            $errors['email'] = "Email must not exceed 100 characters";
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
