# Changelog

All notable changes to the SingaPay Payment Gateway PHP SDK will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-11-24

### Added

- Initial release of SingaPay PHP SDK
- Core SDK architecture with `SingaPay` main class
- Comprehensive configuration management via `Config` class
- HTTP client with automatic retry and interceptor support
- OAuth2 authentication with token caching
- Multiple cache implementations:
  - `ArrayCache` (in-memory, default)
  - `FileCache` (file-based persistence)
  - `RedisCache` (Redis-based distributed caching)
- Security features:
  - HMAC signature generation for API authentication
  - Webhook signature verification
  - Cryptographic request signing
- API resources:
  - Account management
  - Payment Link operations
  - Virtual Account management
  - Disbursement and transfer services
- Exception hierarchy:
  - `SingaPayException` (base exception)
  - `ApiException` (API communication errors)
  - `AuthenticationException` (auth failures)
  - `ValidationException` (request validation errors)
- HTTP interceptors for cross-cutting concerns:
  - `LoggingInterceptor` (PSR-3 compatible logging)
  - `MetricsInterceptor` (performance monitoring)
- Laravel framework integration:
  - Service provider for dependency injection
  - Facade for static access
  - Configuration publishing
- Factory pattern for multi-instance management
- Comprehensive documentation and code examples
- PHPUnit test suite structure
- PHPStan static analysis configuration
- PHP-CS-Fixer coding standards

### Features

- Support for both sandbox and production environments
- Automatic token refresh with configurable TTL
- Exponential backoff retry mechanism
- Custom HTTP headers support
- Request/response interceptors for extensibility
- Performance metrics collection
- Webhook signature validation
- Multi-tenant instance management

### Requirements

- PHP 7.4 or higher
- ext-curl
- ext-json
- ext-redis (optional, for Redis cache)
- GuzzleHTTP 7.0+
- PSR-3 Logger interface

### Security

- Secure token storage with cache isolation
- HMAC-based request signing
- Webhook signature verification
- Automatic token expiration handling
- Secure credential management

## [Unreleased]

### Planned Features

- Additional payment methods support
- Enhanced webhook handling
- More cache adapter implementations
- Additional API resource endpoints
- Improved documentation and examples
- SDK usage analytics
- Rate limiting implementation
- Circuit breaker pattern
- Async request support
- Batch operations
- Webhook event parsing utilities
