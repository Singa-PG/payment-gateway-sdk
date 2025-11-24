<?php

namespace SingaPay\Resources;

use SingaPay\Http\Client;
use SingaPay\Config;
use SingaPay\Security\Authentication;
use SingaPay\Security\Signature;
use SingaPay\Exceptions\ApiException;
use SingaPay\Exceptions\ValidationException;

class Disbursement
{
    private $client;
    private $auth;
    private $config;

    public function __construct(Client $client, Authentication $auth, Config $config)
    {
        $this->client = $client;
        $this->auth = $auth;
        $this->config = $config;
    }

    /**
     * Get list of disbursements
     */
    public function list($accountId, $page = 1, $perPage = 25)
    {
        $headers = $this->getHeaders();
        $response = $this->client->get(
            "/api/v1.0/disbursement/{$accountId}?page={$page}&per_page={$perPage}",
            $headers
        );

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Get disbursement details
     */
    public function get($accountId, $transactionId)
    {
        $headers = $this->getHeaders();
        $response = $this->client->get("/api/v1.0/disbursement/{$accountId}/{$transactionId}", $headers);

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Check transfer fee
     */
    public function checkFee($accountId, $amount, $bankSwiftCode)
    {
        $headers = $this->getHeaders();
        $body = [
            'amount' => $amount,
            'bank_swift_code' => $bankSwiftCode
        ];

        $response = $this->client->post("/api/v1.0/disbursement/{$accountId}/check-fee", $body, $headers);

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Check beneficiary account
     */
    public function checkBeneficiary($bankAccountNumber, $bankSwiftCode)
    {
        $headers = $this->getHeaders();
        $body = [
            'bank_account_number' => $bankAccountNumber,
            'bank_swift_code' => $bankSwiftCode
        ];

        $response = $this->client->post("/api/v1.0/disbursement/check-beneficiary", $body, $headers);

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Transfer funds
     */
    public function transfer($accountId, array $data)
    {
        $this->validateTransferData($data);

        $headers = $this->getTransferHeaders($accountId, $data);
        $response = $this->client->post("/api/v1.0/disbursement/{$accountId}/transfer", $data, $headers);

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
     * Validate transfer data
     */
    private function validateTransferData(array $data)
    {
        $required = ['amount', 'bank_swift_code', 'bank_account_number', 'reference_number'];
        $errors = [];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
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
            'X-PARTNER-ID' => $this->config->getApiKey(),
            'Authorization' => 'Bearer ' . $this->auth->getAccessToken(),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
    }

    /**
     * Get transfer headers with signature
     */
    private function getTransferHeaders($accountId, $body)
    {
        $timestamp = time();
        $endpoint = "/api/v1.0/disbursement/{$accountId}/transfer";
        $accessToken = $this->auth->getAccessToken();

        $signature = Signature::generateDisbursementSignature(
            'POST',
            $endpoint,
            $accessToken,
            $body,
            $timestamp,
            $this->config->getClientSecret()
        );

        return [
            'X-PARTNER-ID' => $this->config->getApiKey(),
            'Authorization' => 'Bearer ' . $accessToken,
            'X-Timestamp' => (string)$timestamp,
            'X-Signature' => $signature,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
    }
}
