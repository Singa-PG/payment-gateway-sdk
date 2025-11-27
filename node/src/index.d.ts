/**
 * SingaPay - Main SDK Class
 *
 * The primary entry point for the SingaPay SDK. This class orchestrates all
 * API resources and provides a unified interface for payment processing,
 * disbursements, virtual accounts, and other SingaPay services.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class SingaPay {
  constructor(config: SingaPayConfig);

  readonly config: Config;
  readonly client: Client;
  readonly auth: Authentication;
  readonly account: Account;
  readonly virtualAccount: VirtualAccount;
  readonly paymentLink: PaymentLink;
  readonly disbursement: Disbursement;
  readonly qris: Qris;
  readonly cardlessWithdrawal: CardlessWithdrawal;
  readonly balanceInquiry: BalanceInquiry;
  readonly statement: Statement;
  readonly paymentLinkHistory: PaymentLinkHistory;
  readonly vaTransaction: VATransaction;

  /**
   * Get SDK version
   */
  static getVersion(): string;

  /**
   * Get configuration object
   */
  getConfig(): Config;

  /**
   * Get HTTP client instance
   */
  getClient(): Client;

  /**
   * Get authentication handler
   */
  getAuth(): Authentication;

  /**
   * Verify webhook signature
   */
  verifyWebhookSignature(
    timestamp: string | number,
    body: any,
    receivedSignature: string
  ): boolean;

  /**
   * Add request/response interceptor
   */
  addInterceptor(interceptor: InterceptorInterface): this;

  /**
   * Get request metrics
   */
  getMetrics(): Metrics;

  /**
   * Force refresh authentication token
   */
  flushAuthCache(): Promise<this>;

  /**
   * Test API connection
   */
  testConnection(): Promise<ConnectionTest>;
}

/**
 * SingaPayFactory - Factory for managing SingaPay SDK instances
 *
 * Provides a centralized factory pattern for creating, managing, and reusing
 * SingaPay SDK instances with different configurations.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class SingaPayFactory {
  /**
   * Create or retrieve a SingaPay SDK instance
   */
  static create(config: SingaPayConfig, name?: string): SingaPay;

  /**
   * Get an existing SingaPay instance by name
   */
  static get(name?: string): SingaPay;

  /**
   * Set default configuration for factory-created instances
   */
  static setDefaultConfig(config: SingaPayConfig): void;

  /**
   * Create instance using default configuration
   */
  static createWithDefault(name?: string): SingaPay;

  /**
   * Check if an instance exists
   */
  static has(name?: string): boolean;

  /**
   * Remove an instance from the factory
   */
  static remove(name?: string): void;

  /**
   * Get all registered instance names
   */
  static getInstanceNames(): string[];

  /**
   * Create multiple instances from a configuration object
   */
  static createMultiple(
    configs: Record<string, SingaPayConfig>
  ): Record<string, SingaPay>;
}

/**
 * Config - SingaPay SDK Configuration Manager
 *
 * Centralized configuration management for the SingaPay SDK.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class Config {
  constructor(config: SingaPayConfig);

  /**
   * Get client ID
   */
  getClientId(): string;

  /**
   * Get client secret
   */
  getClientSecret(): string;

  /**
   * Get API key
   */
  getApiKey(): string;

  /**
   * Get HMAC validation key
   */
  getHmacValidationKey(): string | null;

  /**
   * Get base URL
   */
  getBaseUrl(): string;

  /**
   * Get request timeout
   */
  getTimeout(): number;

  /**
   * Get maximum retry attempts
   */
  getMaxRetries(): number;

  /**
   * Get base retry delay
   */
  getRetryDelay(): number;

  /**
   * Check if auto reauthentication is enabled
   */
  isAutoReauthEnabled(): boolean;

  /**
   * Get default cache TTL
   */
  getCacheTtl(): number;

  /**
   * Get custom headers
   */
  getCustomHeaders(): Record<string, string>;

  /**
   * Check if production environment is configured
   */
  isProduction(): boolean;

  /**
   * Set request timeout
   */
  setTimeout(timeout: number): this;

  /**
   * Set maximum retry attempts
   */
  setMaxRetries(maxRetries: number): this;

  /**
   * Set base retry delay
   */
  setRetryDelay(retryDelay: number): this;

  /**
   * Enable or disable auto reauthentication
   */
  setAutoReauth(enabled: boolean): this;

  /**
   * Set default cache TTL
   */
  setCacheTtl(ttl: number): this;

  /**
   * Add custom HTTP header
   */
  addCustomHeader(name: string, value: string): this;

  /**
   * Remove custom HTTP header
   */
  removeCustomHeader(name: string): this;

  /**
   * Convert configuration to safe object representation
   */
  toObject(): ConfigObject;
}

/**
 * Configuration interface for SingaPay SDK
 */
export interface SingaPayConfig {
  clientId: string;
  clientSecret: string;
  apiKey: string;
  hmacValidationKey?: string;
  environment?: "sandbox" | "production";
  baseUrl?: string;
  timeout?: number;
  maxRetries?: number;
  retryDelay?: number;
  autoReauth?: boolean;
  cacheTtl?: number;
  customHeaders?: Record<string, string>;
}

/**
 * Safe configuration object with masked sensitive data
 */
export interface ConfigObject {
  clientId: string;
  clientSecret: string;
  apiKey: string;
  environment: string;
  baseUrl: string;
  timeout: number;
  maxRetries: number;
  retryDelay: number;
  autoReauth: boolean;
  cacheTtl: number;
}

/**
 * Connection test result
 */
export interface ConnectionTest {
  success: boolean;
  message: string;
  tokenObtained?: boolean;
  errorCode?: number;
}

/**
 * Request metrics data
 */
export interface Metrics {
  totalRequests: number;
  successfulRequests: number;
  failedRequests: number;
  totalResponseTime: number;
  lastRequestTime: number | null;
}

/**
 * Pagination metadata
 */
export interface Pagination {
  current_page: number;
  per_page: number;
  total: number;
  total_pages: number;
}

/**
 * SingaPayException - Base Exception Class
 *
 * The root exception class for all SingaPay SDK exceptions.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class SingaPayException extends Error {
  constructor(message: string, code?: number, originalError?: Error);
  code: number;
  originalError: Error | null;
}

/**
 * ApiException - API Communication Exception
 *
 * Represents errors that occur during API communication with SingaPay servers.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class ApiException extends SingaPayException {}

/**
 * AuthenticationException - Authentication & Authorization Exception
 *
 * Represents errors related to authentication and authorization failures.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class AuthenticationException extends SingaPayException {}

/**
 * ValidationException - Data Validation Exception
 *
 * Represents errors related to data validation failures.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class ValidationException extends SingaPayException {
  constructor(
    message: string,
    errors?: Record<string, string>,
    code?: number,
    originalError?: Error
  );
  errors: Record<string, string>;

  /**
   * Get validation errors
   */
  getErrors(): Record<string, string>;
}

/**
 * Response - HTTP Response Wrapper Class
 *
 * A standardized wrapper for HTTP responses within the SingaPay SDK.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class Response {
  constructor(statusCode: number, body?: any);

  /**
   * Check if the response indicates success
   */
  isSuccess(): boolean;

  /**
   * Get the HTTP status code
   */
  getStatusCode(): number;

  /**
   * Get the complete response body
   */
  getBody(): any;

  /**
   * Get the response data payload
   */
  getData(): any;

  /**
   * Get error information from response
   */
  getError(): any;

  /**
   * Get human-readable message from response
   */
  getMessage(): string | null;

  /**
   * Get error code or status code
   */
  getCode(): number;

  /**
   * Get pagination information
   */
  getPagination(): Pagination | null;

  /**
   * Convert response to plain object
   */
  toJSON(): any;
}

/**
 * Client - HTTP Client for SingaPay API
 *
 * A robust HTTP client specifically designed for interacting with SingaPay APIs.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class Client {
  constructor(config: Config, auth: Authentication);

  /**
   * Add request/response interceptor
   */
  addInterceptor(interceptor: InterceptorInterface): this;

  /**
   * Get all registered interceptors
   */
  getInterceptors(): InterceptorInterface[];

  /**
   * Send GET request
   */
  get(endpoint: string, headers?: Record<string, string>): Promise<Response>;

  /**
   * Send POST request
   */
  post(
    endpoint: string,
    body?: any,
    headers?: Record<string, string>
  ): Promise<Response>;

  /**
   * Send PUT request
   */
  put(
    endpoint: string,
    body?: any,
    headers?: Record<string, string>
  ): Promise<Response>;

  /**
   * Send PATCH request
   */
  patch(
    endpoint: string,
    body?: any,
    headers?: Record<string, string>
  ): Promise<Response>;

  /**
   * Send DELETE request
   */
  delete(endpoint: string, headers?: Record<string, string>): Promise<Response>;

  /**
   * Get request metrics
   */
  getMetrics(): Metrics;
}

/**
 * InterceptorInterface - Base Interface for HTTP Interceptors
 *
 * Defines the standard contract for HTTP request/response interceptors.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export abstract class InterceptorInterface {
  /**
   * Handle outgoing HTTP request
   */
  request(method: string, endpoint: string, options: any): Promise<void>;

  /**
   * Handle successful HTTP response
   */
  response(
    method: string,
    endpoint: string,
    options: any,
    response: Response,
    responseTime: number
  ): Promise<void>;

  /**
   * Handle HTTP error
   */
  error(
    method: string,
    endpoint: string,
    options: any,
    error: Error,
    responseTime: number
  ): Promise<void>;
}

/**
 * LoggingInterceptor - HTTP Request/Response Logger
 *
 * An interceptor implementation that logs HTTP requests, responses, and errors.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class LoggingInterceptor extends InterceptorInterface {
  constructor(logger?: any);
}

/**
 * MetricsInterceptor - HTTP Metrics Collector
 *
 * An interceptor implementation that collects performance metrics and statistics.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class MetricsInterceptor extends InterceptorInterface {
  /**
   * Get complete metrics snapshot
   */
  getMetrics(): Metrics;

  /**
   * Calculate average response time
   */
  getAverageResponseTime(): number;
}

/**
 * CacheInterface - Base Interface for Cache Implementations
 *
 * Defines the standard contract for cache implementations.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export abstract class CacheInterface {
  /**
   * Retrieve item from cache storage
   */
  get(key: string): Promise<any>;

  /**
   * Store item in cache storage
   */
  set(key: string, value: any, ttl?: number): Promise<boolean>;

  /**
   * Remove item from cache storage
   */
  delete(key: string): Promise<boolean>;

  /**
   * Clear entire cache storage
   */
  clear(): Promise<boolean>;

  /**
   * Check for item existence in cache
   */
  has(key: string): Promise<boolean>;
}

/**
 * MemoryCache - In-Memory Cache Implementation
 *
 * A concrete implementation that stores cached items in memory.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class MemoryCache extends CacheInterface {}

/**
 * RedisCache - Redis-Based Cache Implementation
 *
 * A concrete implementation that utilizes Redis as cache storage.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class RedisCache extends CacheInterface {
  constructor(redisClient: any, prefix?: string);
}

/**
 * Authentication - SingaPay API Authentication Handler
 *
 * Handles the complete authentication lifecycle for SingaPay API interactions.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class Authentication {
  constructor(
    config: Config,
    client?: Client | null,
    cache?: CacheInterface | null
  );

  accessToken: string | null;
  cache: CacheInterface | null;

  /**
   * Set HTTP client for authentication requests
   */
  setClient(client: Client): void;

  /**
   * Get current access token
   */
  getAccessToken(): Promise<string>;

  /**
   * Perform authentication with SingaPay API
   */
  authenticate(): Promise<string>;

  /**
   * Refresh access token
   */
  refreshToken(): Promise<string>;

  /**
   * Check if currently authenticated
   */
  isAuthenticated(): boolean;
}

/**
 * Signature - Cryptographic Signature Generator
 *
 * Provides cryptographic signature generation utilities for SingaPay API.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class Signature {
  /**
   * Generate disbursement request signature
   */
  static generateDisbursementSignature(
    method: string,
    endpoint: string,
    accessToken: string,
    body: any,
    timestamp: number,
    clientSecret: string
  ): string;

  /**
   * Verify webhook signature
   */
  static verifyWebhook(
    timestamp: string | number,
    body: any,
    receivedSignature: string,
    hmacKey: string
  ): boolean;
}

/**
 * BaseResource - Base class for all API resources
 *
 * Provides common functionality for all API resource classes.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class BaseResource {
  constructor(client: Client, auth: Authentication, apiKey: string);

  /**
   * Get default headers for API requests
   */
  protected getHeaders(): Record<string, string>;
}

/**
 * Account - Account Management Resource
 *
 * Provides methods for managing merchant accounts.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class Account extends BaseResource {
  /**
   * List all accounts
   */
  list(page?: number, perPage?: number): Promise<any>;

  /**
   * Get account details
   */
  get(accountId: string): Promise<any>;

  /**
   * Create new account
   */
  create(data: CreateAccountData): Promise<any>;

  /**
   * Update account status
   */
  updateStatus(accountId: string, status: "active" | "inactive"): Promise<any>;

  /**
   * Delete account
   */
  delete(accountId: string): Promise<any>;
}

/**
 * Data for creating a new account
 */
export interface CreateAccountData {
  name: string;
  phone: string;
  email: string;
  [key: string]: any;
}

/**
 * VirtualAccount - Virtual Account Management Resource
 *
 * Provides methods for managing virtual accounts.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class VirtualAccount extends BaseResource {
  /**
   * List virtual accounts for an account
   */
  list(accountId: string, page?: number, perPage?: number): Promise<any>;

  /**
   * Get virtual account details
   */
  get(accountId: string, vaId: string): Promise<any>;

  /**
   * Create new virtual account
   */
  create(accountId: string, data: CreateVAData): Promise<any>;

  /**
   * Update virtual account
   */
  update(accountId: string, vaId: string, data: any): Promise<any>;

  /**
   * Delete virtual account
   */
  delete(accountId: string, vaId: string): Promise<any>;
}

/**
 * Data for creating a virtual account
 */
export interface CreateVAData {
  bank_code: string;
  amount: number;
  kind: "temporary" | "permanent";
  expired_at?: string;
  max_usage?: number;
  [key: string]: any;
}

/**
 * PaymentLink - Payment Link Management Resource
 *
 * Provides methods for managing payment links.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class PaymentLink extends BaseResource {
  /**
   * List payment links for an account
   */
  list(accountId: string, page?: number, perPage?: number): Promise<any>;

  /**
   * Get payment link details
   */
  get(accountId: string, paymentLinkId: string): Promise<any>;

  /**
   * Create new payment link
   */
  create(accountId: string, data: CreatePaymentLinkData): Promise<any>;

  /**
   * Update payment link
   */
  update(accountId: string, paymentLinkId: string, data: any): Promise<any>;

  /**
   * Delete payment link
   */
  delete(accountId: string, paymentLinkId: string): Promise<any>;

  /**
   * Get available payment methods
   */
  getAvailablePaymentMethods(): Promise<any>;
}

/**
 * Data for creating a payment link
 */
export interface CreatePaymentLinkData {
  reff_no: string;
  title: string;
  total_amount: number;
  items: PaymentLinkItem[];
  max_usage?: number;
  [key: string]: any;
}

/**
 * Payment link item details
 */
export interface PaymentLinkItem {
  name: string;
  quantity: number;
  unit_price: number;
}

/**
 * Disbursement - Disbursement Management Resource
 *
 * Provides methods for managing disbursements and transfers.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class Disbursement extends BaseResource {
  constructor(client: Client, auth: Authentication, config: Config);

  /**
   * List disbursements for an account
   */
  list(accountId: string, page?: number, perPage?: number): Promise<any>;

  /**
   * Get disbursement details
   */
  get(accountId: string, transactionId: string): Promise<any>;

  /**
   * Check disbursement fee
   */
  checkFee(
    accountId: string,
    amount: number,
    bankSwiftCode: string
  ): Promise<any>;

  /**
   * Check beneficiary account
   */
  checkBeneficiary(
    bankAccountNumber: string,
    bankSwiftCode: string
  ): Promise<any>;

  /**
   * Execute transfer
   */
  transfer(accountId: string, data: TransferData): Promise<any>;
}

/**
 * Data for transfer operation
 */
export interface TransferData {
  amount: number;
  bank_swift_code: string;
  bank_account_number: string;
  reference_number: string;
  beneficiary_name?: string;
  [key: string]: any;
}

/**
 * Qris - QRIS Management Resource
 *
 * Provides methods for managing QRIS transactions.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class Qris extends BaseResource {
  /**
   * List QRIS transactions for an account
   */
  list(accountId: string, page?: number, perPage?: number): Promise<any>;

  /**
   * Get QRIS transaction details
   */
  get(accountId: string, qrisId: string): Promise<any>;

  /**
   * Generate QRIS code
   */
  generate(accountId: string, data: GenerateQrisData): Promise<any>;

  /**
   * Delete QRIS transaction
   */
  delete(qrisId: string): Promise<any>;
}

/**
 * Data for generating QRIS code
 */
export interface GenerateQrisData {
  amount: number;
  expired_at: string;
  tip_indicator?: "fixed_amount" | "percentage";
  tip_value?: number;
  [key: string]: any;
}

/**
 * CardlessWithdrawal - Cardless Withdrawal Management Resource
 *
 * Provides methods for managing cardless withdrawal transactions.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class CardlessWithdrawal extends BaseResource {
  /**
   * List cardless withdrawals for an account
   */
  list(accountId: string, page?: number, perPage?: number): Promise<any>;

  /**
   * Get cardless withdrawal details
   */
  get(accountId: string, transactionId: string): Promise<any>;

  /**
   * Create cardless withdrawal
   */
  create(accountId: string, data: CreateCardlessData): Promise<any>;

  /**
   * Cancel cardless withdrawal
   */
  cancel(accountId: string, transactionId: string): Promise<any>;

  /**
   * Delete cardless withdrawal
   */
  delete(accountId: string, transactionId: string): Promise<any>;
}

/**
 * Data for creating cardless withdrawal
 */
export interface CreateCardlessData {
  withdraw_amount: number;
  payment_vendor_code: string;
  [key: string]: any;
}

/**
 * BalanceInquiry - Balance Inquiry Resource
 *
 * Provides methods for checking account and merchant balances.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class BalanceInquiry extends BaseResource {
  /**
   * Get account balance
   */
  getAccountBalance(accountId: string): Promise<any>;

  /**
   * Get merchant balance
   */
  getMerchantBalance(): Promise<any>;
}

/**
 * Statement - Statement Management Resource
 *
 * Provides methods for managing account statements.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class Statement extends BaseResource {
  /**
   * List statements for an account
   */
  list(
    accountId: string,
    page?: number,
    perPage?: number,
    filters?: StatementFilters
  ): Promise<any>;

  /**
   * Get statement details
   */
  get(accountId: string, statementId: string): Promise<any>;
}

/**
 * Filters for statement listing
 */
export interface StatementFilters {
  start_date?: string;
  end_date?: string;
}

/**
 * PaymentLinkHistory - Payment Link History Resource
 *
 * Provides methods for managing payment link history.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class PaymentLinkHistory extends BaseResource {
  /**
   * List payment link history for an account
   */
  list(accountId: string, page?: number, perPage?: number): Promise<any>;

  /**
   * Get payment link history details
   */
  get(accountId: string, historyId: string): Promise<any>;
}

/**
 * VATransaction - Virtual Account Transaction Resource
 *
 * Provides methods for managing virtual account transactions.
 *
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class VATransaction extends BaseResource {
  /**
   * List VA transactions for an account
   */
  list(accountId: string, page?: number, perPage?: number): Promise<any>;

  /**
   * Get VA transaction details
   */
  get(accountId: string, transactionId: string): Promise<any>;
}

/**
 * Sandbox environment constant
 */
export const ENV_SANDBOX: "sandbox";

/**
 * Production environment constant
 */
export const ENV_PRODUCTION: "production";

/**
 * Default request timeout in seconds
 */
export const DEFAULT_TIMEOUT: 30000;

/**
 * Default maximum retry attempts
 */
export const DEFAULT_MAX_RETRIES: 3;

/**
 * Default retry delay in milliseconds
 */
export const DEFAULT_RETRY_DELAY: 1000;

/**
 * Default auto reauthentication setting
 */
export const DEFAULT_AUTO_REAUTH: true;

/**
 * Default cache TTL in seconds
 */
export const DEFAULT_CACHE_TTL: 3600;

/**
 * Sandbox API base URL
 */
export const SANDBOX_URL: "https://sandbox-payment-b2b.singapay.id";

/**
 * Production API base URL
 */
export const PRODUCTION_URL: "https://payment-b2b.singapay.id";

export const SDK_VERSION: "";
