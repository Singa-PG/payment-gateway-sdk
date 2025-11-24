<?php

namespace SingaPay\Resources;

use SingaPay\Http\Client;
use SingaPay\Security\Authentication;
use SingaPay\Exceptions\ApiException;
use SingaPay\Exceptions\ValidationException;

class PaymentLink
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
     * Get list of payment links
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
     * Get payment link details
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
     * Create payment link
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
     * Update payment link
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
     * Delete payment link
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
     * Get available payment methods
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
     * Validate create payment link data
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
