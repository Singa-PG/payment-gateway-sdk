<?php

namespace SingaPay\Resources;

use SingaPay\Http\Client;
use SingaPay\Security\Authentication;
use SingaPay\Exceptions\ApiException;

/**
 * Virtual Account Transaction Resource
 * 
 * Handles VA transaction operations
 */
class VATransaction
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
     * List VA transactions
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
            "/api/v1.0/va-transactions/{$accountId}?page={$page}&per_page={$perPage}",
            $headers
        );

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Get specific VA transaction
     * 
     * @param string $accountId Account ID
     * @param string $transactionId Transaction ID
     * @return array
     */
    public function get($accountId, $transactionId)
    {
        $headers = $this->getHeaders();
        $response = $this->client->get(
            "/api/v1.0/va-transactions/{$accountId}/{$transactionId}",
            $headers
        );

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
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
