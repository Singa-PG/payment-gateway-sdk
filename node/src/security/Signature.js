import crypto from "crypto";

/**
 * Signature - Cryptographic Signature Generator
 *
 * Provides cryptographic signature generation utilities for SingaPay API authentication
 * and request verification. This class implements the signature algorithms required
 * for secure communication with SingaPay disbursement and authentication endpoints.
 *
 * All methods are static and stateless, making them thread-safe and easy to use
 * without instance creation.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class Signature {
  /**
   * Generate disbursement request signature
   *
   * Creates a HMAC-SHA512 signature for disbursement API requests. The signature
   * is generated from a concatenated string of request components including method,
   * endpoint, access token, request body hash, and timestamp.
   *
   * @param {string} method HTTP method (GET, POST, PUT, DELETE, etc.)
   * @param {string} endpoint API endpoint path (e.g., '/v1/disbursements')
   * @param {string} accessToken Current access token for authentication
   * @param {object|string|null} body Request body object or JSON string (null for empty body)
   * @param {number} timestamp Current UNIX timestamp in seconds
   * @param {string} clientSecret Client secret key for HMAC signing
   * @returns {string} Base64-encoded HMAC-SHA512 signature
   *
   * @example
   * const signature = Signature.generateDisbursementSignature(
   *   'POST',
   *   '/v1/disbursements',
   *   'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
   *   { amount: 100000, recipient: '1234567890' },
   *   1672531200,
   *   'your-client-secret'
   * );
   * // Returns: 'L9XlZ2P4aX7RkKjH8gN3mQ1wV6cB8yT...'
   *
   * @throws {Error} If cryptographic operations fail
   */
  static generateDisbursementSignature(
    method,
    endpoint,
    accessToken,
    body,
    timestamp,
    clientSecret
  ) {
    const bodyString = body
      ? typeof body === "string"
        ? body
        : JSON.stringify(body)
      : "";
    const hashedBody = crypto
      .createHash("sha256")
      .update(bodyString)
      .digest("hex")
      .toLowerCase();

    const stringToSign = `${method}:${endpoint}:${accessToken}:${hashedBody}:${timestamp}`;

    return crypto
      .createHmac("sha512", clientSecret)
      .update(stringToSign)
      .digest("base64");
  }

  /**
   * Generate authentication signature
   *
   * Creates a HMAC-SHA512 signature for API authentication requests. The signature
   * is generated from client ID, client secret, and current date components.
   * This method is typically used for obtaining access tokens.
   *
   * @param {string} clientId Client identifier provided by SingaPay
   * @param {string} clientSecret Client secret key provided by SingaPay
   * @returns {object} Authentication signature object with properties:
   * @returns {number} returns.timestamp Current UNIX timestamp in seconds
   * @returns {string} returns.signature Hex-encoded HMAC-SHA512 signature
   * @returns {string} returns.date Current date in YYYYMMDD format
   * @returns {string} returns.payload The original payload string used for signing
   *
   * @example
   * const authData = Signature.generateAuthSignature(
   *   'your-client-id',
   *   'your-client-secret'
   * );
   * // Returns: {
   * //   timestamp: 1672531200,
   * //   signature: 'a1b2c3d4e5f6...',
   * //   date: '20230101',
   * //   payload: 'your-client-id_your-client-secret_20230101'
   * // }
   *
   * @throws {Error} If cryptographic operations fail
   */
  static generateAuthSignature(clientId, clientSecret) {
    const timestamp = Math.floor(Date.now() / 1000);

    const date = new Date(timestamp * 1000);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    const currentDate = year + month + day;

    const payload = `${clientId}_${clientSecret}_${currentDate}`;

    const signature = crypto
      .createHmac("sha512", clientSecret)
      .update(payload)
      .digest("hex");

    return {
      timestamp,
      signature,
      date: currentDate,
      payload,
    };
  }
}
