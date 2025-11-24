<?php

namespace SingaPay\Resources;

use SingaPay\Http\Client;
use SingaPay\Security\Authentication;
use SingaPay\Exceptions\ApiException;
use SingaPay\Exceptions\ValidationException;

class VirtualAccount
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
     * Get list of virtual accounts
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
     * Create virtual account
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
     * Update virtual account
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
     * Delete virtual account
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
     * Validate create VA data
     */
    private function validateCreateData(array $data)
    {
        $required = ['bank_code', 'amount', 'kind'];
        $errors = [];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = "The {$field} field is required";
            }
        }

        if (isset($data['kind']) && !in_array($data['kind'], ['temporary', 'permanent'])) {
            $errors['kind'] = "Kind must be either 'temporary' or 'permanent'";
        }

        if (isset($data['kind']) && $data['kind'] === 'temporary' && empty($data['expired_at'])) {
            $errors['expired_at'] = "The expired_at field is required for temporary VA";
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
