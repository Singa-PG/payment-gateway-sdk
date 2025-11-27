<?php

namespace SingaPay\Resources;

use SingaPay\Http\Client;
use SingaPay\Security\Authentication;
use SingaPay\Exceptions\ApiException;
use SingaPay\Exceptions\ValidationException;

/**
 * QRIS Resource
 * 
 * Handles QRIS dynamic QR code operations
 */
class Qris
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
     * List QRIS codes
     * 
     * @param string $accountId Account ID
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array
     */
    public function list($accountId, $page = 1, $perPage = 25)
    {
        $headers = $this->getHeaders();
        $response = $this->client->get(
            "/api/v1.0/qris-dynamic/{$accountId}?page={$page}&per_page={$perPage}",
            $headers
        );

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Get specific QRIS code
     * 
     * @param string $accountId Account ID
     * @param string $qrisId QRIS ID
     * @return array
     */
    public function get($accountId, $qrisId)
    {
        $headers = $this->getHeaders();
        $response = $this->client->get(
            "/api/v1.0/qris-dynamic/{$accountId}/show/{$qrisId}",
            $headers
        );

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Generate QRIS code
     * 
     * @param string $accountId Account ID
     * @param array $data QRIS data
     * @return array
     */
    public function generate($accountId, array $data)
    {
        $this->validateGenerateData($data);

        $headers = $this->getHeaders();
        $response = $this->client->post(
            "/api/v1.0/qris-dynamic/{$accountId}/generate-qr",
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
     * Delete QRIS code
     * 
     * @param string $qrisId QRIS ID
     * @return array
     */
    public function delete($qrisId)
    {
        $headers = $this->getHeaders();
        $response = $this->client->delete(
            "/api/v1.0/qris-dynamic/{$qrisId}/delete",
            $headers
        );

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    private function validateGenerateData(array $data)
    {
        $required = ['amount', 'expired_at'];
        $errors = [];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = "The {$field} field is required";
            }
        }

        if (isset($data['amount']) && (!is_numeric($data['amount']) || $data['amount'] <= 0)) {
            $errors['amount'] = "Amount must be a positive number";
        }

        if (isset($data['tip_indicator']) && !in_array($data['tip_indicator'], ['fixed_amount', 'percentage'])) {
            $errors['tip_indicator'] = "Tip indicator must be either 'fixed_amount' or 'percentage'";
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }

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
