<?php

namespace SingaPay\Resources;

use SingaPay\Http\Client;
use SingaPay\Security\Authentication;
use SingaPay\Exceptions\ApiException;
use SingaPay\Exceptions\ValidationException;

class Account
{
    private $client;
    private $auth;
    private $apiKey;

    public function __construct(Client $client, Authentication $auth, $apiKey)
    {
        $this->client = $client;
        $this->auth = $auth;
        $this->apiKey = $apiKey;
    }

    /**
     * Get list of accounts
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
     * Get account details
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
     * Create new account
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
     * Update account status
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
     * Delete account
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
     * Validate create account data
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
    }

    /**
     * Get request headers
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
