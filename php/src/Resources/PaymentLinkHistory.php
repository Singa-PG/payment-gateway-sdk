<?php

namespace SingaPay\Resources;

use SingaPay\Http\Client;
use SingaPay\Security\Authentication;
use SingaPay\Exceptions\ApiException;

/**
 * Payment Link History Resource
 * 
 * Handles payment link transaction history operations
 */
class PaymentLinkHistory
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
     * List payment link histories
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
            "/api/v1.0/payment-link-histories/{$accountId}?page={$page}&per_page={$perPage}",
            $headers
        );

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Get specific payment link history
     * 
     * @param string $accountId Account ID
     * @param string $historyId History ID
     * @return array
     */
    public function get($accountId, $historyId)
    {
        $headers = $this->getHeaders();
        $response = $this->client->get(
            "/api/v1.0/payment-link-histories/{$accountId}/{$historyId}",
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
