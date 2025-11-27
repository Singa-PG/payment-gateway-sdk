<?php

namespace SingaPay\Resources;

use SingaPay\Http\Client;
use SingaPay\Security\Authentication;
use SingaPay\Exceptions\ApiException;
use SingaPay\Exceptions\ValidationException;

/**
 * Cardless Withdrawal (CLWD) Resource
 * 
 * Handles cardless withdrawal operations
 */
class CardlessWithdrawal
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
     * List cardless withdrawals
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
            "/api/v1.0/cardless-withdrawals/{$accountId}?page={$page}&per_page={$perPage}",
            $headers
        );

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Get specific cardless withdrawal
     * 
     * @param string $accountId Account ID
     * @param string $transactionId Transaction ID
     * @return array
     */
    public function get($accountId, $transactionId)
    {
        $headers = $this->getHeaders();
        $response = $this->client->get(
            "/api/v1.0/cardless-withdrawals/{$accountId}/show/{$transactionId}",
            $headers
        );

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Create cardless withdrawal
     * 
     * @param string $accountId Account ID
     * @param array $data Withdrawal data
     * @return array
     */
    public function create($accountId, array $data)
    {
        $this->validateCreateData($data);

        $headers = $this->getHeaders();
        $response = $this->client->post(
            "/api/v1.0/cardless-withdrawals/{$accountId}",
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
     * Cancel cardless withdrawal
     * 
     * @param string $accountId Account ID
     * @param string $transactionId Transaction ID
     * @return array
     */
    public function cancel($accountId, $transactionId)
    {
        $headers = $this->getHeaders();
        $response = $this->client->patch(
            "/api/v1.0/cardless-withdrawals/{$accountId}/cancel/{$transactionId}",
            null,
            $headers
        );

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Delete cardless withdrawal
     * 
     * @param string $accountId Account ID
     * @param string $transactionId Transaction ID
     * @return array
     */
    public function delete($accountId, $transactionId)
    {
        $headers = $this->getHeaders();
        $response = $this->client->delete(
            "/api/v1.0/cardless-withdrawals/{$accountId}/delete/{$transactionId}",
            $headers
        );

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    private function validateCreateData(array $data)
    {
        $required = ['withdraw_amount', 'payment_vendor_code'];
        $errors = [];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = "The {$field} field is required";
            }
        }

        if (isset($data['withdraw_amount']) && (!is_numeric($data['withdraw_amount']) || $data['withdraw_amount'] <= 0)) {
            $errors['withdraw_amount'] = "Withdraw amount must be a positive number";
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
