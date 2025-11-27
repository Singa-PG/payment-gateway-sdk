<?php

namespace SingaPay\Resources;

use SingaPay\Http\Client;
use SingaPay\Security\Authentication;
use SingaPay\Exceptions\ApiException;

/**
 * Statement Resource
 * 
 * Handles account statement operations
 */
class Statement
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
     * List account statements
     * 
     * @param string $accountId Account ID
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param array $filters Optional filters (start_date, end_date)
     * @return array
     */
    public function list($accountId, $page = 1, $perPage = 25, array $filters = [])
    {
        $headers = $this->getHeaders();
        $queryParams = "page={$page}&per_page={$perPage}";

        if (!empty($filters['start_date'])) {
            $queryParams .= "&start_date=" . urlencode($filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $queryParams .= "&end_date=" . urlencode($filters['end_date']);
        }

        $response = $this->client->get(
            "/api/v1.0/statements/{$accountId}?{$queryParams}",
            $headers
        );

        if (!$response->isSuccess()) {
            throw new ApiException($response->getMessage(), $response->getCode());
        }

        return $response->getData();
    }

    /**
     * Get specific statement
     * 
     * @param string $accountId Account ID
     * @param string $statementId Statement/Transaction ID
     * @return array
     */
    public function get($accountId, $statementId)
    {
        $headers = $this->getHeaders();
        $response = $this->client->get(
            "/api/v1.0/statements/{$accountId}/{$statementId}",
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
