<?php

namespace SingaPay\Resources;

use SingaPay\Http\Client;
use SingaPay\Security\Authentication;
use SingaPay\Exceptions\ApiException;

/**
 * Balance Inquiry Resource
 * 
 * Handles balance inquiry operations
 */
class BalanceInquiry
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
     * Get account balance
     * 
     * @param string $accountId Account ID
     * @return array Balance information including held, available, pending, and total balance
     */
    public function getAccountBalance($accountId)
    {
        $headers = $this->getHeaders();
        $response = $this->client->get("/api/v1.0/balance-inquiry/{$accountId}", $headers);

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Get merchant balance (all accounts)
     * 
     * @return array Total merchant balance across all accounts
     */
    public function getMerchantBalance()
    {
        $headers = $this->getHeaders();
        $response = $this->client->get("/api/v1.0/balance-inquiry", $headers);

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
