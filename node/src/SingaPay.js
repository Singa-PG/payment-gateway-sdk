import { MemoryCache } from "./cache/MemoryCache.js";
import { Config } from "./Config.js";
import { Client } from "./http/Client.js";
import { Account } from "./resources/Account.js";
import { BalanceInquiry } from "./resources/BalanceInquiry.js";
import { CardlessWithdrawal } from "./resources/CardlessWithdrawal.js";
import { Disbursement } from "./resources/Disbursement.js";
import { PaymentLink } from "./resources/PaymentLink.js";
import { PaymentLinkHistory } from "./resources/PaymentLinkHistory.js";
import { Qris } from "./resources/Qris.js";
import { Statement } from "./resources/Statement.js";
import { VATransaction } from "./resources/VATransaction.js";
import { VirtualAccount } from "./resources/VirtualAccount.js";
import { Authentication } from "./security/Authentication.js";
import { Signature } from "./security/Signature.js";
import { SDK_VERSION } from "./version.js";

/**
 * SingaPay - Main SDK Class
 *
 * The primary entry point for the SingaPay SDK. This class orchestrates all
 * API resources and provides a unified interface for payment processing,
 * disbursements, virtual accounts, and other SingaPay services.
 *
 * The SDK supports both sandbox and production environments, automatic
 * authentication, request retries, caching, and comprehensive error handling.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class SingaPay {
  /**
   * @constant {string} version Current SDK version
   */
  static version = SDK_VERSION;

  /**
   * Creates a new SingaPay SDK instance
   *
   * @param {object|Config} config Configuration object or Config instance
   * @param {string} config.clientId Client identifier provided by SingaPay
   * @param {string} config.clientSecret Client secret key provided by SingaPay
   * @param {string} config.apiKey API key provided by SingaPay
   * @param {string} [config.environment=sandbox] API environment: 'sandbox' or 'production'
   * @param {string} [config.hmacValidationKey] HMAC key for webhook signature verification
   *
   * @example
   * // Basic initialization
   * const singapay = new SingaPay({
   *   clientId: 'your-client-id',
   *   clientSecret: 'your-client-secret',
   *   apiKey: 'your-api-key',
   *   environment: 'sandbox'
   * });
   *
   * @example
   * // Production initialization
   * const singapay = new SingaPay({
   *   clientId: 'prod-client-id',
   *   clientSecret: 'prod-client-secret',
   *   apiKey: 'prod-api-key',
   *   environment: 'production',
   *   hmacValidationKey: 'your-hmac-key'
   * });
   *
   * @throws {Error} If required configuration is missing or invalid
   */
  constructor(config) {
    this.config = new Config(config);
    this.initializeDependencies();
    this.initializeResources();
  }

  /**
   * Initialize SDK dependencies
   *
   * Sets up the HTTP client, authentication handler, and cache system.
   * This method can be overridden for custom dependency injection.
   *
   * @private
   * @param {Client|null} client Custom HTTP client instance (optional)
   * @param {Authentication|null} auth Custom authentication handler (optional)
   */
  initializeDependencies(client = null, auth = null) {
    if (client === null) {
      this.auth =
        auth || new Authentication(this.config, null, new MemoryCache());
      this.client = new Client(this.config, this.auth);
      this.auth.setClient(this.client);
    } else {
      this.client = client;
      this.auth = auth;
    }
  }

  /**
   * Initialize API resource classes
   *
   * Creates instances of all available API resource classes and makes them
   * accessible as properties of the SingaPay instance.
   *
   * @private
   */
  initializeResources() {
    const apiKey = this.config.getApiKey();

    this.account = new Account(this.client, this.auth, apiKey);
    this.virtualAccount = new VirtualAccount(this.client, this.auth, apiKey);
    this.paymentLink = new PaymentLink(this.client, this.auth, apiKey);
    this.disbursement = new Disbursement(this.client, this.auth, this.config);
    this.qris = new Qris(this.client, this.auth, apiKey);
    this.cardlessWithdrawal = new CardlessWithdrawal(
      this.client,
      this.auth,
      apiKey
    );
    this.balanceInquiry = new BalanceInquiry(this.client, this.auth, apiKey);
    this.statement = new Statement(this.client, this.auth, apiKey);
    this.paymentLinkHistory = new PaymentLinkHistory(
      this.client,
      this.auth,
      apiKey
    );
    this.vaTransaction = new VATransaction(this.client, this.auth, apiKey);
  }

  /**
   * Get SDK version
   *
   * @static
   * @returns {string} Current SDK version string
   *
   * @example
   * console.log(`Using SingaPay SDK v${SingaPay.getVersion()}`);
   */
  static getVersion() {
    return SingaPay.version;
  }

  /**
   * Get configuration object
   *
   * @returns {Config} Configuration instance
   *
   * @example
   * const config = singapay.getConfig();
   * console.log(`Environment: ${config.isProduction() ? 'Production' : 'Sandbox'}`);
   */
  getConfig() {
    return this.config;
  }

  /**
   * Get HTTP client instance
   *
   * @returns {Client} HTTP client instance
   *
   * @example
   * const client = singapay.getClient();
   * const metrics = client.getMetrics();
   */
  getClient() {
    return this.client;
  }

  /**
   * Get authentication handler
   *
   * @returns {Authentication} Authentication instance
   *
   * @example
   * const auth = singapay.getAuth();
   * const isAuthenticated = auth.isAuthenticated();
   */
  getAuth() {
    return this.auth;
  }

  /**
   * Verify webhook signature
   *
   * Validates the authenticity of incoming webhook requests by verifying
   * the HMAC signature against the provided timestamp and body.
   *
   * @param {string} timestamp Webhook timestamp
   * @param {string|object} body Webhook request body
   * @param {string} receivedSignature Received HMAC signature
   * @returns {boolean} True if signature is valid
   *
   * @throws {Error} If HMAC validation key is not configured
   *
   * @example
   * // In webhook handler
   * const isValid = singapay.verifyWebhookSignature(
   *   req.headers['x-timestamp'],
   *   req.body,
   *   req.headers['x-signature']
   * );
   *
   * if (!isValid) {
   *   return res.status(401).json({ error: 'Invalid signature' });
   * }
   */
  verifyWebhookSignature(timestamp, body, receivedSignature) {
    const hmacKey = this.config.getHmacValidationKey();

    if (!hmacKey) {
      throw new Error(
        "HMAC Validation Key is required for webhook verification"
      );
    }

    return Signature.verifyWebhook(timestamp, body, receivedSignature, hmacKey);
  }

  /**
   * Add request/response interceptor
   *
   * Registers an interceptor for monitoring or modifying HTTP requests
   * and responses. Useful for logging, metrics, or custom headers.
   *
   * @param {object} interceptor Interceptor instance
   * @returns {SingaPay} Returns this for method chaining
   *
   * @example
   * singapay.addInterceptor({
   *   request: async (method, endpoint, options) => {
   *     console.log(`Request: ${method} ${endpoint}`);
   *   }
   * });
   */
  addInterceptor(interceptor) {
    this.client.addInterceptor(interceptor);
    return this;
  }

  /**
   * Get request metrics
   *
   * Returns collected metrics from the MetricsInterceptor including
   * request counts, success rates, and response times.
   *
   * @returns {object} Metrics data
   *
   * @example
   * const metrics = singapay.getMetrics();
   * console.log(`Success rate: ${metrics.successRate}%`);
   * console.log(`Total requests: ${metrics.totalRequests}`);
   */
  getMetrics() {
    return this.client.getMetrics();
  }

  /**
   * Force refresh authentication token
   *
   * Invalidates the current access token and obtains a new one.
   * Useful when token expiration is suspected or for security reasons.
   *
   * @returns {Promise<SingaPay>} Promise resolving to this instance
   *
   * @example
   * await singapay.flushAuthCache();
   * console.log('Authentication cache flushed');
   */
  async flushAuthCache() {
    await this.auth.refreshToken();
    return this;
  }

  /**
   * Test API connection
   *
   * Verifies that the SDK can successfully authenticate and connect
   * to the SingaPay API. Useful for health checks and initialization validation.
   *
   * @returns {Promise<{success: boolean, message: string, tokenObtained?: boolean, errorCode?: number}>} Connection test result
   * @returns {boolean} returns.success True if connection successful
   * @returns {string} returns.message Success or error message
   * @returns {number} [returns.errorCode] Error code if connection failed
   *
   * @example
   * const result = await singapay.testConnection();
   * if (result.success) {
   *   console.log('SDK is properly configured');
   * } else {
   *   console.error('Configuration error:', result.message);
   * }
   */
  async testConnection() {
    try {
      const token = await this.auth.getAccessToken();
      return {
        success: true,
        message: "Connection successful",
        tokenObtained: !!token,
      };
    } catch (error) {
      return {
        success: false,
        message: error.message,
        errorCode: error.code,
      };
    }
  }
}
