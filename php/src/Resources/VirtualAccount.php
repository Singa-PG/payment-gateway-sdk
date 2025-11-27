<?php

namespace SingaPay\Resources;

use SingaPay\Http\Client;
use SingaPay\Security\Authentication;
use SingaPay\Exceptions\ApiException;
use SingaPay\Exceptions\ValidationException;

/**
 * Virtual Account Resource
 * 
 * Handles all Virtual Account operations including:
 * - Listing virtual accounts
 * - Creating new virtual accounts (both temporary and permanent)
 * - Retrieving virtual account details
 * - Updating virtual accounts
 * - Deleting virtual accounts
 * 
 * @package SingaPay\Resources
 * @author PT. Abadi Singapay Indonesia
 */
class VirtualAccount
{
    /**
     * HTTP client instance
     * @var Client
     */
    private $client;

    /**
     * Authentication instance
     * @var Authentication
     */
    private $auth;

    /**
     * API key for partner identification
     * @var string
     */
    private $apiKey;

    /**
     * Constructor
     * 
     * @param Client $client HTTP client for API requests
     * @param Authentication $auth Authentication handler
     * @param string $apiKey Partner API key
     */
    public function __construct(Client $client, Authentication $auth, $apiKey)
    {
        $this->client = $client;
        $this->auth = $auth;
        $this->apiKey = $apiKey;
    }

    /**
     * Get list of virtual accounts for a specific account
     * 
     * Corresponds to: GET /api/v1.0/virtual-accounts/:account_id
     * 
     * @param string $accountId The account ID
     * @param int $page Page number for pagination (default: 1)
     * @param int $perPage Number of items per page (default: 25, max: 25)
     * @return array Virtual accounts list with pagination data
     * @throws ApiException When API request fails
     * 
     * @example
     * $va->list('01K9EH4HTX4921FCYE01RPVQ87', 1, 25);
     */
    public function list($accountId, $page = 1, $perPage = 25)
    {
        $headers = $this->getHeaders();
        $response = $this->client->get(
            "/api/v1.0/virtual-accounts/{$accountId}?page={$page}&per_page={$perPage}",
            $headers
        );

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Get virtual account details
     * 
     * Corresponds to: GET /api/v1.0/virtual-accounts/:account_id/:va_id
     * 
     * @param string $accountId The account ID
     * @param string $vaId The virtual account ID
     * @return array Virtual account details including:
     *               - id: Virtual account ID
     *               - number: Virtual account number
     *               - bank: Bank information (short_name, number, swift_code)
     *               - amount: Amount value and currency
     *               - status: Account status (active/inactive)
     *               - kind: Account type (temporary/permanent)
     *               - current_usage: Current usage count
     * @throws ApiException When API request fails
     * 
     * @example
     * $va->get('01K9EH4HTX4921FCYE01RPVQ87', '01K9EH4HTX4921FCYE01RPVQ87');
     */
    public function get($accountId, $vaId)
    {
        $headers = $this->getHeaders();
        $response = $this->client->get("/api/v1.0/virtual-accounts/{$accountId}/{$vaId}", $headers);

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Create a new virtual account
     * 
     * Corresponds to: POST /api/v1.0/virtual-accounts/:account_id
     * 
     * @param string $accountId The account ID
     * @param array $data Virtual account creation data:
     *               - bank_code: Bank code (e.g., 'BRI', 'MAYBANK')
     *               - amount: Transaction amount (numeric)
     *               - name: Account holder name (optional)
     *               - kind: Account type - 'temporary' or 'permanent' (required)
     *               - expired_at: Expiry timestamp (13-digit milliseconds, required for temporary VA)
     *               - max_usage: Maximum usage count (max: 255)
     * @return array Created virtual account data
     * @throws ApiException When API request fails
     * @throws ValidationException When validation fails
     * 
     * @example
     * // Create permanent VA
     * $va->create('01K9EH4HTX4921FCYE01RPVQ87', [
     *     'bank_code' => 'BRI',
     *     'amount' => 100000,
     *     'name' => 'Customer Name',
     *     'kind' => 'permanent'
     * ]);
     * 
     * // Create temporary VA
     * $va->create('01K9EH4HTX4921FCYE01RPVQ87', [
     *     'bank_code' => 'MAYBANK',
     *     'amount' => 25000,
     *     'name' => 'Customer Name',
     *     'kind' => 'temporary',
     *     'expired_at' => 1763390642000,
     *     'max_usage' => 10
     * ]);
     */
    public function create($accountId, array $data)
    {
        $this->validateCreateData($data);

        $headers = $this->getHeaders();
        $response = $this->client->post("/api/v1.0/virtual-accounts/{$accountId}", $data, $headers);

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
     * Update an existing virtual account
     * 
     * Corresponds to: PUT /api/v1.0/virtual-accounts/:account_id/:va_id
     * 
     * @param string $accountId The account ID
     * @param string $vaId The virtual account ID to update
     * @param array $data Update data:
     *               - amount: New amount (required)
     *               - name: New account name (optional)
     *               - status: New status (required)
     *               - expired_at: New expiry (for temporary VA)
     *               - max_usage: New max usage (for temporary VA)
     * @return array Updated virtual account data
     * @throws ApiException When API request fails
     * 
     * @example
     * // Update permanent VA
     * $va->update('01K9EH4HTX4921FCYE01RPVQ87', '01K35WXRGZ6FZQ0RJWP5R7FAPT', [
     *     'amount' => 150000,
     *     'name' => 'Updated permanent VA',
     *     'status' => 'active'
     * ]);
     * 
     * // Update temporary VA
     * $va->update('01K9EH4HTX4921FCYE01RPVQ87', '01K380TM3G2SHBPS9GYDP7WFMB', [
     *     'amount' => 150000,
     *     'name' => 'Updated Temporary VA',
     *     'status' => 'active',
     *     'expired_at' => 1731628800000,
     *     'max_usage' => 10
     * ]);
     */
    public function update($accountId, $vaId, array $data)
    {
        $headers = $this->getHeaders();
        $response = $this->client->put(
            "/api/v1.0/virtual-accounts/{$accountId}/{$vaId}",
            $data,
            $headers
        );

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Delete a virtual account
     * 
     * Corresponds to: DELETE /api/v1.0/virtual-accounts/:account_id/:va_id
     * 
     * Note: Virtual accounts with existing transactions cannot be deleted (returns 403)
     * 
     * @param string $accountId The account ID
     * @param string $vaId The virtual account ID to delete
     * @return array Delete confirmation with success message
     * @throws ApiException When API request fails or VA has transactions
     * 
     * @example
     * $va->delete('01K9EH4HTX4921FCYE01RPVQ87', '01K9ER6ZX33SGQPPQ3D8DZ21K0');
     * // Returns: ['message' => 'Virtual Account delete successfully']
     */
    public function delete($accountId, $vaId)
    {
        $headers = $this->getHeaders();
        $response = $this->client->delete("/api/v1.0/virtual-accounts/{$accountId}/{$vaId}", $headers);

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Validate virtual account creation data
     * 
     * @param array $data Creation data to validate
     * @throws ValidationException When validation fails
     */
    private function validateCreateData(array $data)
    {
        $required = ['bank_code', 'amount', 'kind'];
        $errors = [];

        // Check required fields
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = "The {$field} field is required";
            }
        }

        // Validate kind field
        if (isset($data['kind']) && !in_array($data['kind'], ['temporary', 'permanent'])) {
            $errors['kind'] = "Kind must be either 'temporary' or 'permanent'";
        }

        // Validate expired_at for temporary VA
        if (isset($data['kind']) && $data['kind'] === 'temporary' && empty($data['expired_at'])) {
            $errors['expired_at'] = "The expired_at field is required for temporary VA";
        }

        // Validate max_usage if provided
        if (isset($data['max_usage']) && $data['max_usage'] > 255) {
            $errors['max_usage'] = "Max Usage is 255";
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }

    /**
     * Get standard request headers for API calls
     * 
     * @return array Headers for API requests
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
