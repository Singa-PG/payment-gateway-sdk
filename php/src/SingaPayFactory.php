<?php

namespace SingaPay;

/**
 * SingaPay Factory for Multi-Instance Management
 * 
 * Provides a factory pattern implementation for creating and managing multiple
 * SingaPay SDK instances with different configurations. This enables partners
 * to work with multiple merchant accounts, environments, or configurations
 * simultaneously within the same application.
 * 
 * @package SingaPay
 * @author PT. Abadi Singapay Indonesia
  */
class SingaPayFactory
{
    /**
     * @var array Registry of SingaPay instances indexed by instance name
     */
    private static $instances = [];

    /**
     * @var array|null Default configuration for factory-created instances
     */
    private static $defaultConfig = null;

    /**
     * Create or retrieve SingaPay instance with specific configuration
     * 
     * Creates a new SingaPay SDK instance with the provided configuration and
     * registers it under the specified name. If an instance with the same name
     * already exists, returns the existing instance (singleton pattern per name).
     * 
     * @param array $config Configuration array for the SingaPay instance
     * @param string $name Unique identifier for the instance (default: 'default')
     * 
     * @return SingaPay Configured SingaPay SDK instance
     * 
     * @throws \InvalidArgumentException If configuration is invalid
     * 
     * @example
     * // Create primary instance
     * $primary = SingaPayFactory::create([
     *     'client_id' => 'primary-client-id',
     *     'client_secret' => 'primary-secret',
     *     'environment' => 'production'
     * ], 'primary');
     * 
     * // Create secondary instance
     * $secondary = SingaPayFactory::create([
     *     'client_id' => 'secondary-client-id',
     *     'client_secret' => 'secondary-secret',
     *     'environment' => 'sandbox'
     * ], 'secondary');
     */
    public static function create(array $config, $name = 'default')
    {
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        $instance = new SingaPay($config);
        self::$instances[$name] = $instance;

        return $instance;
    }

    /**
     * Retrieve existing SingaPay instance by name
     * 
     * Returns a previously created SingaPay instance identified by the given name.
     * Useful for accessing SDK instances across different parts of an application
     * without passing object references.
     * 
     * @param string $name Instance identifier (default: 'default')
     * 
     * @return SingaPay Existing SingaPay SDK instance
     * 
     * @throws \InvalidArgumentException If no instance exists with the specified name
     * 
     * @example
     * // Retrieve instance in different part of application
     * $singapay = SingaPayFactory::get('primary');
     * $accounts = $singapay->account->list();
     * 
     * // Use default instance
     * $defaultInstance = SingaPayFactory::get();
     */
    public static function get($name = 'default')
    {
        if (!isset(self::$instances[$name])) {
            throw new \InvalidArgumentException("SingaPay instance '{$name}' not found");
        }

        return self::$instances[$name];
    }

    /**
     * Set default configuration for factory instances
     * 
     * Defines a default configuration that will be used when creating instances
     * via createWithDefault() method. This is useful for applications that
     * primarily use a single configuration but may need multiple instances.
     * 
     * @param array $config Default configuration array
     * 
     * @return void
     * 
     * @example
     * // Set default configuration
     * SingaPayFactory::setDefaultConfig([
     *     'client_id' => 'default-client-id',
     *     'client_secret' => 'default-secret',
     *     'environment' => 'sandbox',
     *     'timeout' => 60
     * ]);
     * 
     * // Later create instances with default config
     * $instance1 = SingaPayFactory::createWithDefault('instance1');
     * $instance2 = SingaPayFactory::createWithDefault('instance2');
     */
    public static function setDefaultConfig(array $config)
    {
        self::$defaultConfig = $config;
    }

    /**
     * Create instance using pre-defined default configuration
     * 
     * Creates a new SingaPay instance using the configuration previously set
     * via setDefaultConfig(). This ensures consistency across multiple instances
     * and simplifies instance creation in multi-tenant applications.
     * 
     * @param string $name Unique identifier for the instance (default: 'default')
     * 
     * @return SingaPay Configured SingaPay SDK instance
     * 
     * @throws \InvalidArgumentException If default configuration is not set
     * 
     * @example
     * // Set default config once during application bootstrap
     * SingaPayFactory::setDefaultConfig($appConfig['singapay']);
     * 
     * // Create multiple instances with same base configuration
     * $clientA = SingaPayFactory::createWithDefault('client_a');
     * $clientB = SingaPayFactory::createWithDefault('client_b');
     */
    public static function createWithDefault($name = 'default')
    {
        if (self::$defaultConfig === null) {
            throw new \InvalidArgumentException("Default configuration not set");
        }

        return self::create(self::$defaultConfig, $name);
    }

    /**
     * Check if instance with given name exists
     * 
     * Verifies whether a SingaPay instance has been registered under the
     * specified name. Useful for conditional instance creation or validation.
     * 
     * @param string $name Instance identifier to check (default: 'default')
     * 
     * @return bool True if instance exists, false otherwise
     * 
     * @example
     * if (SingaPayFactory::has('primary')) {
     *     $singapay = SingaPayFactory::get('primary');
     * } else {
     *     $singapay = SingaPayFactory::create($config, 'primary');
     * }
     */
    public static function has($name = 'default')
    {
        return isset(self::$instances[$name]);
    }

    /**
     * Remove instance from registry
     * 
     * Unregisters and removes a SingaPay instance from the factory registry.
     * This does not destroy the instance but removes the factory's reference
     * to it, allowing for garbage collection if no other references exist.
     * 
     * @param string $name Instance identifier to remove (default: 'default')
     * 
     * @return void
     * 
     * @example
     * // Remove instance when no longer needed
     * SingaPayFactory::remove('temporary_instance');
     * 
     * // Verify removal
     * if (!SingaPayFactory::has('temporary_instance')) {
     *     echo "Instance successfully removed";
     * }
     */
    public static function remove($name = 'default')
    {
        unset(self::$instances[$name]);
    }

    /**
     * Get names of all registered instances
     * 
     * Returns an array containing the names of all currently registered
     * SingaPay instances. Useful for debugging, logging, or instance management.
     * 
     * @return array List of registered instance names
     * 
     * @example
     * $instanceNames = SingaPayFactory::getInstanceNames();
     * // Returns: ['default', 'primary', 'secondary', 'backup']
     * 
     * foreach ($instanceNames as $name) {
     *     $instance = SingaPayFactory::get($name);
     *     $metrics = $instance->getMetrics();
     *     logMetrics($name, $metrics);
     * }
     */
    public static function getInstanceNames()
    {
        return array_keys(self::$instances);
    }

    /**
     * Create multiple instances from configuration array
     * 
     * Bulk creation of SingaPay instances from an associative array where
     * keys are instance names and values are configuration arrays.
     * Efficient for setting up multiple merchant accounts or environments.
     * 
     * @param array $configs Associative array of [name => configuration] pairs
     * 
     * @return array All registered instances (including previously created ones)
     * 
     * @example
     * // Bulk create instances for multiple merchants
     * $configs = [
     *     'merchant_a' => [
     *         'client_id' => 'merchant_a_id',
     *         'client_secret' => 'merchant_a_secret',
     *         'environment' => 'production'
     *     ],
     *     'merchant_b' => [
     *         'client_id' => 'merchant_b_id',
     *         'client_secret' => 'merchant_b_secret',
     *         'environment' => 'sandbox'
     *     ],
     *     'merchant_c' => [
     *         'client_id' => 'merchant_c_id',
     *         'client_secret' => 'merchant_c_secret',
     *         'environment' => 'production'
     *     ]
     * ];
     * 
     * $instances = SingaPayFactory::createMultiple($configs);
     * 
     * // Now access any merchant instance
     * $merchantA = SingaPayFactory::get('merchant_a');
     * $merchantB = SingaPayFactory::get('merchant_b');
     */
    public static function createMultiple(array $configs)
    {
        foreach ($configs as $name => $config) {
            self::create($config, $name);
        }

        return self::$instances;
    }
}
