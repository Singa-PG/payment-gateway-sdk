<?php

namespace SingaPay\Security;

/**
 * SingaPay Security Signature Handler
 * 
 * Provides cryptographic signature generation and verification for SingaPay API security.
 * This class implements HMAC-based signature algorithms for API authentication,
 * request validation, and webhook security as per SingaPay security specifications.
 * 
 * @package SingaPay\Security
 * @author PT. Abadi Singapay Indonesia
  */
class Signature
{
    /**
     * Generate HMAC SHA512 signature for V1.1 API authentication
     * 
     * Creates a cryptographic signature for API authentication requests using HMAC-SHA512.
     * This signature is required for obtaining access tokens and authenticating with
     * SingaPay's identity service. The signature incorporates client credentials and
     * timestamp to prevent replay attacks.
     * 
     * @param string $clientId Partner's unique client identifier provided by SingaPay
     * @param string $clientSecret Partner's confidential client secret for cryptographic signing
     * @param string|null $date Date string in 'Ymd' format (optional, defaults to current date)
     * 
     * @return string HMAC-SHA512 hexadecimal signature string (128 characters)
     * 
     * @throws \InvalidArgumentException If clientId or clientSecret are empty
     * 
     * @example
     * $signature = Signature::generateV11(
     *     'your-client-id',
     *     'your-client-secret'
     * );
     * 
     * // Use signature in authentication headers
     * $headers = [
     *     'X-Signature' => $signature,
     *     'X-CLIENT-ID' => $clientId
     * ];
     */
    public static function generateV11($clientId, $clientSecret, $date = null)
    {
        if (empty($clientId) || empty($clientSecret)) {
            throw new \InvalidArgumentException('Client ID and Client Secret are required for signature generation');
        }

        if ($date === null) {
            $date = date('Ymd');
        }

        $payload = $clientId . '_' . $clientSecret . '_' . $date;

        return hash_hmac('sha512', $payload, $clientSecret);
    }

    /**
     * Generate signature for disbursement transfer requests
     * 
     * Creates a secure signature for disbursement and fund transfer API calls.
     * This signature ensures request integrity and authenticity by incorporating
     * request method, endpoint, access token, request body, and timestamp.
     * The signature follows SingaPay's disbursement security protocol.
     * 
     * @param string $method HTTP method (GET, POST, PUT, DELETE, PATCH)
     * @param string $endpoint API endpoint path (e.g., '/api/v1.0/disbursement/transfer')
     * @param string $accessToken Valid access token obtained from authentication
     * @param array $body Request body data as associative array
     * @param int $timestamp Unix timestamp of request generation
     * @param string $clientSecret Partner's client secret for cryptographic signing
     * 
     * @return string HMAC-SHA512 hexadecimal signature string (128 characters)
     * 
     * @throws \InvalidArgumentException If required parameters are missing or invalid
     * 
     * @example
     * $signature = Signature::generateDisbursementSignature(
     *     'POST',
     *     '/api/v1.0/disbursement/account-123/transfer',
     *     'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
     *     [
     *         'amount' => 50000,
     *         'bank_account_number' => '1234567890',
     *         'bank_swift_code' => 'BRINIDJA'
     *     ],
     *     time(),
     *     'your-client-secret'
     * );
     * 
     * // Include signature in disbursement request headers
     * $headers = [
     *     'X-Signature' => $signature,
     *     'X-Timestamp' => $timestamp
     * ];
     */
    public static function generateDisbursementSignature($method, $endpoint, $accessToken, $body, $timestamp, $clientSecret)
    {
        if (empty($method) || empty($endpoint) || empty($accessToken) || empty($clientSecret)) {
            throw new \InvalidArgumentException('All parameters are required for disbursement signature generation');
        }

        if (!is_array($body)) {
            throw new \InvalidArgumentException('Body parameter must be an array');
        }

        $bodyJson = json_encode($body);
        $hashedBody = hash('sha256', $bodyJson);

        $stringToSign = implode(':', [
            strtoupper($method),
            $endpoint,
            $accessToken,
            $hashedBody,
            $timestamp
        ]);

        return hash_hmac('sha512', $stringToSign, $clientSecret);
    }

    /**
     * Verify webhook signature authenticity
     * 
     * Validates incoming webhook requests to ensure they originate from SingaPay
     * and have not been tampered with during transmission. This verification
     * prevents unauthorized webhook processing and ensures data integrity.
     * 
     * @param string $timestamp Timestamp from webhook request headers (X-Timestamp)
     * @param mixed $body Webhook request body (can be array, object, or JSON string)
     * @param string $receivedSignature Signature from webhook request headers (X-Signature)
     * @param string $hmacKey HMAC validation key provided by SingaPay for webhook verification
     * 
     * @return bool True if signature is valid and webhook is authentic, false otherwise
     * 
     * @throws \InvalidArgumentException If required parameters are missing
     * 
     * @example
     * // In webhook handler
     * $timestamp = $_SERVER['HTTP_X_TIMESTAMP'];
     * $receivedSignature = $_SERVER['HTTP_X_SIGNATURE'];
     * $rawBody = file_get_contents('php://input');
     * 
     * $isValid = Signature::verifyWebhook(
     *     $timestamp,
     *     $rawBody,
     *     $receivedSignature,
     *     'your-hmac-validation-key'
     * );
     * 
     * if ($isValid) {
     *     // Process authentic webhook
     *     $webhookData = json_decode($rawBody, true);
     *     processWebhook($webhookData);
     * } else {
     *     // Log and reject suspicious webhook
     *     http_response_code(401);
     *     exit('Invalid webhook signature');
     * }
     */
    public static function verifyWebhook($timestamp, $body, $receivedSignature, $hmacKey)
    {
        if (empty($timestamp) || empty($receivedSignature) || empty($hmacKey)) {
            throw new \InvalidArgumentException('Timestamp, signature, and HMAC key are required for webhook verification');
        }

        $bodyJson = is_string($body) ? $body : json_encode($body);
        $bodyHashed = hash('sha256', $bodyJson);

        $signaturePayload = $timestamp . $bodyHashed;
        $expectedSignature = hash_hmac('sha256', $signaturePayload, $hmacKey);

        return hash_equals($expectedSignature, $receivedSignature);
    }
}
