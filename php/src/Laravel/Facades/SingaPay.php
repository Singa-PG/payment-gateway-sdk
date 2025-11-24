<?php

namespace SingaPay\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * SingaPay Laravel Facade
 * 
 * Provides a static interface to the SingaPay SDK within Laravel applications.
 * This facade enables convenient, expressive access to SingaPay functionality
 * without requiring dependency injection in every usage context.
 * 
 * @package SingaPay\Laravel\Facades
 * @author PT. Abadi Singapay Indonesia
 * 
 * 
 * @method static string getVersion() Get current SDK version
 * @method static \SingaPay\Config getConfig() Get configuration instance
 * @method static \SingaPay\Http\Client getClient() Get HTTP client instance
 * @method static \SingaPay\Security\Authentication getAuth() Get authentication instance
 * @method static bool verifyWebhookSignature(string $timestamp, mixed $body, string $receivedSignature) Verify webhook signature authenticity
 * @method static \SingaPay\SingaPay addInterceptor(mixed $interceptor) Add interceptor to HTTP client
 * @method static array getMetrics() Get request metrics and statistics
 * @method static \SingaPay\SingaPay flushAuthCache() Flush authentication cache and force token refresh
 * @method static array testConnection() Test API connection and authentication
 * @method static \SingaPay\Resources\Account account Account management resource
 * @method static \SingaPay\Resources\PaymentLink paymentLink Payment link management resource
 * @method static \SingaPay\Resources\VirtualAccount virtualAccount Virtual account management resource
 * @method static \SingaPay\Resources\Disbursement disbursement Disbursement and transfer management resource
 */
class SingaPay extends Facade
{
    /**
     * Get the registered name of the component
     * 
     * Returns the service container binding key for the SingaPay SDK instance.
     * This method is required by Laravel's Facade base class to resolve
     * the underlying instance from the service container.
     * 
     * @return string Service container binding key
     * 
     * @example
     * // The facade will resolve to the SingaPay instance registered in the container
     * SingaPay::testConnection();
     */
    protected static function getFacadeAccessor()
    {
        return \SingaPay\SingaPay::class;
    }
}
