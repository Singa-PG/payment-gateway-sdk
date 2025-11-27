<?php

namespace SingaPay\Resources;

use SingaPay\Http\Client;
use SingaPay\Config;
use SingaPay\Security\Authentication;
use SingaPay\Security\Signature;
use SingaPay\Exceptions\ApiException;
use SingaPay\Exceptions\ValidationException;

/**
 * Disbursement Resource
 * 
 * Handles all disbursement (fund transfer) operations including:
 * - Listing disbursement transactions
 * - Retrieving disbursement details
 * - Checking transfer fees
 * - Validating beneficiary accounts
 * - Executing fund transfers with comprehensive error handling
 * 
 * @package SingaPay\Resources
 */
class Disbursement
{
    /**
     * HTTP client instance
     * @var Client
     */
    private $client;

    /**
     * Authentication instance
     * @var Authentication
     */
    private $auth;

    /**
     * Configuration instance
     * @var Config
     */
    private $config;

    /**
     * Constructor
     * 
     * @param Client $client HTTP client for API requests
     * @param Authentication $auth Authentication handler
     * @param Config $config Configuration handler
     */
    public function __construct(Client $client, Authentication $auth, Config $config)
    {
        $this->client = $client;
        $this->auth = $auth;
        $this->config = $config;
    }

    /**
     * Get list of disbursement transactions
     * 
     * Corresponds to: GET /api/v1.0/disbursement/:account_id
     * 
     * @param string $accountId The account ID
     * @param int $page Page number for pagination (default: 1)
     * @param int $perPage Number of items per page (default: 25, max: 25)
     * @return array Disbursement transactions list with pagination data including:
     *               - transaction_id: Unique transaction identifier
     *               - status: Transaction status (success, pending, failed)
     *               - bank: Bank details (code, account_name, account_number)
     *               - gross_amount: Transfer amount with currency
     *               - fee: Transfer fee details
     *               - net_amount: Net amount after fee deduction
     *               - timestamps: Post and processed timestamps
     *               - balance_after: Account balance after transaction
     *               - notes: Transaction notes
     * @throws ApiException When API request fails
     * 
     * @example
     * $disbursements = $disbursement->list('01K9EH4HTX4921FCYE01RPVQ87', 1, 25);
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
     * Get disbursement transaction details
     * 
     * Corresponds to: GET /api/v1.0/disbursement/:account_id/:transaction_id
     * 
     * @param string $accountId The account ID
     * @param string $transactionId The transaction ID to retrieve
     * @return array Disbursement transaction details including:
     *               - transaction_id: Unique transaction identifier
     *               - status: Transaction status (success, pending, failed)
     *               - bank: Bank details (code, account_name, account_number)
     *               - gross_amount: Transfer amount with currency
     *               - fee: Transfer fee details
     *               - net_amount: Net amount after fee deduction
     *               - timestamps: Post and processed timestamps
     *               - balance_after: Account balance after transaction
     *               - notes: Transaction notes
     * @throws ApiException When API request fails
     * 
     * @example
     * $transaction = $disbursement->get('01K9EH4HTX4921FCYE01RPVQ87', '1312220251024134833219');
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
     * Check transfer fee for a disbursement
     * 
     * Corresponds to: POST /api/v1.0/disbursement/:account_id/check-fee
     * 
     * @param string $accountId The account ID
     * @param float $amount Transfer amount to check fee for
     * @param string $bankSwiftCode Bank swift code (e.g., 'BRINIDJA', 'BBBAIDJA')
     * @return array Fee calculation details including:
     *               - gross_amount: Original transfer amount
     *               - transfer_fee: Calculated transfer fee
     *               - net_amount: Net amount after fee deduction
     *               - currency: Currency code (IDR)
     *               - beneficiary: Bank beneficiary information
     * @throws ApiException When API request fails or insufficient balance
     * 
     * @example
     * $feeDetails = $disbursement->checkFee(
     *     '01K9EH4HTX4921FCYE01RPVQ87',
     *     50000,
     *     'BRINIDJA'
     * );
     * 
     * // Returns:
     * // [
     * //     'gross_amount' => '50000',
     * //     'transfer_fee' => '3000.00',
     * //     'net_amount' => '47000',
     * //     'currency' => 'IDR',
     * //     'beneficiary' => [
     * //         'full_name' => 'Bank Rakyat Indonesia',
     * //         'short_name' => 'BRI'
     * //     ]
     * // ]
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
     * Check beneficiary account validity
     * 
     * Corresponds to: POST /api/v1.0/disbursement/check-beneficiary
     * 
     * @param string $bankAccountNumber Beneficiary bank account number
     * @param string $bankSwiftCode Bank swift code (e.g., 'BRINIDJA', 'BBBAIDJA')
     * @return array Beneficiary validation details including:
     *               - status: Validation status ('valid' or 'invalid')
     *               - bank_name: Bank name
     *               - bank_number_code: Bank number code
     *               - bank_swift_code: Bank swift code
     *               - bank_account_number: Validated account number
     *               - bank_account_name: Account holder name
     * @throws ApiException When API request fails or account is invalid
     * 
     * @example
     * $beneficiary = $disbursement->checkBeneficiary('091701064838533', 'BRINIDJA');
     * 
     * // Returns:
     * // [
     * //     'status' => 'valid',
     * //     'bank_name' => 'BRI',
     * //     'bank_number_code' => '002',
     * //     'bank_swift_code' => 'BRINIDJA',
     * //     'bank_account_number' => '091701064838533',
     * //     'bank_account_name' => 'ADNAN KASIM'
     * // ]
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
     * Execute fund transfer (disbursement)
     * 
     * Corresponds to: POST /api/v1.0/disbursement/:account_id/transfer
     * 
     * Note: This endpoint requires HMAC signature authentication (X-Signature header)
     * 
     * @param string $accountId The account ID
     * @param array $data Transfer data:
     *               - amount: Transfer amount (required)
     *               - bank_swift_code: Bank swift code (required)
     *               - bank_account_number: Beneficiary account number (required)
     *               - reference_number: Unique reference number (required)
     *               - notes: Transfer notes (optional)
     * @return array Transfer result with transaction details including:
     *               - transaction_id: Unique transaction identifier
     *               - reference_number: Your reference number
     *               - status: Transaction status (success, pending, failed)
     *               - bank: Bank details
     *               - gross_amount: Transfer amount
     *               - fee: Transfer fee
     *               - net_amount: Net amount
     *               - timestamps: Post and processed timestamps
     *               - balance_after: Account balance after transfer
     *               - notes: Transfer notes
     * @throws ApiException When API request fails with specific error codes:
     *               - 422: Validation error (missing required fields)
     *               - 409: Reference number already exists or being processed
     *               - 403: Insufficient balance, account limit exceeded, transaction not permitted
     *               - 404: Invalid account
     *               - 401: Invalid signature
     *               - 500: Server error
     *               - 504: Request timeout
     * @throws ValidationException When input validation fails
     * 
     * @example
     * // Successful transfer
     * $transfer = $disbursement->transfer('01K2KVRQQP45234X9T3YWG1FKT', [
     *     'amount' => 50000,
     *     'bank_swift_code' => 'BRINIDJA',
     *     'bank_account_number' => '1234567890000',
     *     'reference_number' => '1211223456711',
     *     'notes' => 'Payment for services'
     * ]);
     * 
     * // Pending transfer
     * $transfer = $disbursement->transfer('01K2KVRQQP45234X9T3YWG1FKT', [
     *     'amount' => 50000,
     *     'bank_swift_code' => 'BRINIDJA',
     *     'bank_account_number' => '1234567893333',
     *     'reference_number' => '122345611',
     *     'notes' => 'Payment for services'
     * ]);
     * 
     * // Error handling example
     * try {
     *     $transfer = $disbursement->transfer($accountId, $data);
     * } catch (ApiException $e) {
     *     switch ($e->getCode()) {
     *         case 403:
     *             if (strpos($e->getMessage(), 'Insufficient Balance') !== false) {
     *                 // Handle insufficient balance
     *             } elseif (strpos($e->getMessage(), 'Account Limit Exceed') !== false) {
     *                 // Handle account limit
     *             }
     *             break;
     *         case 409:
     *             // Handle duplicate reference number
     *             break;
     *     }
     * }
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
     * Validate transfer data before execution
     * 
     * @param array $data Transfer data to validate
     * @throws ValidationException When validation fails
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

        if (isset($data['amount']) && (!is_numeric($data['amount']) || $data['amount'] <= 0)) {
            $errors['amount'] = "Amount must be a positive number";
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }

    /**
     * Get standard request headers for API calls
     * 
     * @return array Headers for API requests
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
     * Get transfer headers with HMAC signature
     * 
     * Transfer endpoints require additional security with X-Signature header
     * generated using HMAC-SHA512 algorithm with client secret
     * 
     * @param string $accountId The account ID
     * @param array $body Request body data
     * @return array Headers with signature for transfer requests
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
