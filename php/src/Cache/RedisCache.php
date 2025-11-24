<?php

namespace SingaPay\Cache;

/**
 * SingaPay Redis Cache Implementation
 * 
 * High-performance Redis-based cache implementation providing distributed
 * caching capabilities with persistence and advanced data structures. This
 * cache provider is ideal for production environments requiring high
 * availability, scalability, and cross-process cache synchronization.
 * 
 * The implementation leverages Redis' built-in TTL expiration and provides
 * efficient serialization of PHP data structures. Supports both existing
 * Redis connections and automatic connection establishment with flexible
 * configuration options for complex deployment scenarios.
 * 
 * @package SingaPay\Cache
 * @author PT. Abadi Singapay Indonesia
  */
class RedisCache implements CacheInterface
{
    /**
     * @var \Redis Redis client instance
     */
    private $redis;

    /**
     * @var string Key prefix for namespace isolation
     */
    private $prefix;

    /**
     * Initialize Redis Cache
     * 
     * Constructs a new Redis cache instance with either an existing Redis
     * connection or configuration for creating a new connection. Supports
     * connection pooling, authentication, database selection, and key
     * namespacing for cache isolation.
     * 
     * @param mixed $redis Existing Redis instance or configuration array
     * @param string $prefix Key prefix for cache namespace isolation
     */
    public function __construct($redis = null, $prefix = 'singapay_')
    {
        if ($redis instanceof \Redis) {
            $this->redis = $redis;
        } else {
            $this->redis = new \Redis();
            $config = $redis ?? [];

            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? 6379;
            $timeout = $config['timeout'] ?? 0;
            $password = $config['password'] ?? null;
            $database = $config['database'] ?? 0;

            $this->redis->connect($host, $port, $timeout);

            if ($password) {
                $this->redis->auth($password);
            }

            $this->redis->select($database);
        }

        $this->prefix = $prefix;
    }

    /**
     * Retrieve item from Redis cache
     * 
     * Fetches a cached value from Redis storage using the prefixed key.
     * Automatically handles PHP serialization to restore complex data
     * structures. Returns null if the key does not exist or has expired.
     * 
     * @param string $key Unique identifier for the cached item
     * 
     * @return mixed Cached value if found, null otherwise
     */
    public function get($key)
    {
        $value = $this->redis->get($this->prefix . $key);

        if ($value === false) {
            return null;
        }

        return unserialize($value);
    }

    /**
     * Store item in Redis cache
     * 
     * Persists a value in Redis with the specified key and optional TTL.
     * Uses PHP serialization to store complex data structures and leverages
     * Redis' native TTL support for automatic expiration. Supports both
     * persistent storage (no TTL) and time-limited caching.
     * 
     * @param string $key Unique identifier for the cached item
     * @param mixed $value Data to be cached (must be serializable)
     * @param int|null $ttl Time-to-live in seconds, or null for no expiration
     * 
     * @return bool True if the item was successfully stored in Redis
     */
    public function set($key, $value, $ttl = null)
    {
        $serialized = serialize($value);

        if ($ttl !== null) {
            return $this->redis->setex($this->prefix . $key, $ttl, $serialized);
        }

        return $this->redis->set($this->prefix . $key, $serialized);
    }

    /**
     * Remove item from Redis cache
     * 
     * Deletes the specified key from Redis storage. Returns true if the
     * key was successfully deleted or did not exist, providing idempotent
     * behavior for cache invalidation operations.
     * 
     * @param string $key Unique identifier for the cached item to remove
     * 
     * @return bool True if the key was deleted or did not exist
     */
    public function delete($key)
    {
        return $this->redis->del($this->prefix . $key) > 0;
    }

    /**
     * Clear all SingaPay cache items from Redis
     * 
     * Removes all cache entries that match the configured key prefix,
     * effectively performing a namespace-specific cache clearance. This
     * operation only affects keys within the SingaPay cache namespace,
     * preserving other application data in Redis.
     * 
     * @return bool True if all matching keys were successfully deleted
     */
    public function clear()
    {
        $keys = $this->redis->keys($this->prefix . '*');

        if (empty($keys)) {
            return true;
        }

        return $this->redis->del($keys) > 0;
    }

    /**
     * Check for item existence in Redis cache
     * 
     * Verifies whether a cached item exists in Redis storage. Utilizes
     * Redis' native EXISTS command for efficient key existence checking
     * without retrieving the actual data.
     * 
     * @param string $key Unique identifier for the cached item to check
     * 
     * @return bool True if the key exists in Redis, false otherwise
     */
    public function has($key)
    {
        return $this->redis->exists($this->prefix . $key) > 0;
    }

    /**
     * Get underlying Redis client instance
     * 
     * Returns the Redis client instance for advanced Redis operations
     * beyond the basic cache interface. This provides access to Redis
     * features like transactions, pub/sub, and data structure operations
     * while maintaining the established connection.
     * 
     * @return \Redis Redis client instance
     */
    public function getRedis()
    {
        return $this->redis;
    }
}
