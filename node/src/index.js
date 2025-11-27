/**
 * SingaPay Payment Gateway SDK
 *
 * Official Node.js SDK for integrating with SingaPay Payment Gateway services.
 * Provides comprehensive APIs for payment processing, disbursements, virtual accounts,
 * QRIS, and other financial services in Indonesia.
 *
 * @module @singapay/payment-gateway
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 * @version 1.0.0
 */

/**
 * Core configuration and main SDK classes
 * @namespace Core
 */
export { Config } from "./Config.js";
export { SingaPay } from "./SingaPay.js";
export { SingaPayFactory } from "./SingaPayFactory.js";
export { SDK_VERSION } from "./version.js";

/**
 * Custom exception classes for error handling
 * @namespace Exceptions
 */
export {
  ApiException,
  AuthenticationException,
  SingaPayException,
  ValidationException,
} from "./exceptions/SingaPayException.js";

/**
 * Cache interfaces and implementations for token and data caching
 * @namespace Cache
 */
export { CacheInterface } from "./cache/CacheInterface.js";
export { MemoryCache } from "./cache/MemoryCache.js";
export { RedisCache } from "./cache/RedisCache.js";

/**
 * HTTP client, response handling, and interceptors
 * @namespace HTTP
 */
export { Client } from "./http/Client.js";
export { InterceptorInterface } from "./http/interceptors/InterceptorInterface.js";
export { LoggingInterceptor } from "./http/interceptors/LoggingInterceptor.js";
export { MetricsInterceptor } from "./http/interceptors/MetricsInterceptor.js";
export { Response } from "./http/Response.js";

/**
 * Authentication and cryptographic utilities
 * @namespace Security
 */
export { Authentication } from "./security/Authentication.js";
export { Signature } from "./security/Signature.js";

/**
 * API resource classes for various SingaPay services
 * @namespace Resources
 */
export { Account } from "./resources/Account.js";
export { BalanceInquiry } from "./resources/BalanceInquiry.js";
export { BaseResource } from "./resources/BaseResource.js";
export { CardlessWithdrawal } from "./resources/CardlessWithdrawal.js";
export { Disbursement } from "./resources/Disbursement.js";
export { PaymentLink } from "./resources/PaymentLink.js";
export { PaymentLinkHistory } from "./resources/PaymentLinkHistory.js";
export { Qris } from "./resources/Qris.js";
export { Statement } from "./resources/Statement.js";
export { VATransaction } from "./resources/VATransaction.js";
export { VirtualAccount } from "./resources/VirtualAccount.js";

/**
 * Configuration constants and default values
 * @namespace Constants
 */
export * from "./constants.js";
