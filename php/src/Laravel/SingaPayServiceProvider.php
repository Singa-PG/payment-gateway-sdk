<?php

namespace SingaPay\Laravel;

use Illuminate\Support\ServiceProvider;
use SingaPay\SingaPay;

/**
 * SingaPay Laravel Service Provider
 * 
 * Provides seamless integration of SingaPay PHP SDK with Laravel applications.
 * This service provider handles configuration management, dependency injection,
 * and package publishing for Laravel-based implementations.
 * 
 * @package SingaPay\Laravel
 * @author PT. Abadi Singapay Indonesia
  */
class SingaPayServiceProvider extends ServiceProvider
{
    /**
     * Register SingaPay services in the Laravel container
     * 
     * Registers the SingaPay SDK as a singleton in Laravel's service container
     * and merges package configuration with application configuration.
     * This ensures the SDK is properly initialized and available throughout
     * the Laravel application via dependency injection.
     * 
     * @return void
     * 
     * @example
     * // The SDK becomes available via Laravel's container
     * $singapay = app(SingaPay::class);
     * 
     * // Or through dependency injection in controllers
     * public function processPayment(SingaPay $singapay)
     * {
     *     $paymentLink = $singapay->paymentLink->create(...);
     * }
     */
    public function register()
    {
        // Merge package configuration with application configuration
        // Allows partners to override default settings in their config/singapay.php
        $this->mergeConfigFrom(__DIR__ . '/config/singapay.php', 'singapay');

        // Register SingaPay as singleton in service container
        // Ensures single instance throughout application lifecycle
        $this->app->singleton(SingaPay::class, function ($app) {
            $config = [
                'client_id' => config('singapay.client_id'),
                'client_secret' => config('singapay.client_secret'),
                'api_key' => config('singapay.api_key'),
                'hmac_validation_key' => config('singapay.hmac_validation_key'),
                'environment' => config('singapay.environment'),
                'timeout' => config('singapay.timeout'),
                'max_retries' => config('singapay.max_retries'),
                'retry_delay' => config('singapay.retry_delay'),
                'auto_reauth' => config('singapay.auto_reauth'),
                'cache_ttl' => config('singapay.cache_ttl')
            ];

            return new SingaPay($config);
        });
    }

    /**
     * Bootstrap SingaPay package services
     * 
     * Handles package bootstrapping tasks including configuration publishing.
     * This method is called after all services have been registered and allows
     * the package to publish its configuration files to the application.
     * 
     * @return void
     * 
     * @example
     * // Partners can publish configuration using Artisan
     * php artisan vendor:publish --provider="SingaPay\Laravel\SingaPayServiceProvider" --tag="singapay-config"
     * 
     * // This creates config/singapay.php in the application
     */
    public function boot()
    {
        // Publish configuration file to application config directory
        // Allows partners to customize SDK settings for their environment
        $this->publishes([
            __DIR__ . '/config/singapay.php' => config_path('singapay.php'),
        ], 'singapay-config');
    }
}
