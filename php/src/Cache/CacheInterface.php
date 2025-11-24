<?php

namespace SingaPay\Cache;

/**
 * SingaPay Cache Interface
 * 
 * Defines the standard contract for cache implementations used throughout
 * the SingaPay SDK. This interface provides a consistent caching API that
 * supports various cache storage backends while maintaining interoperability
 * with the SDK's authentication and request caching mechanisms.
 * 
 * Implementations of this interface can utilize different storage technologies
 * including in-memory arrays, Redis, Memcached, file systems, or databases,
 * allowing partners to choose caching strategies that match their infrastructure
 * requirements and performance needs.
 * 
 * @package SingaPay\Cache
 * @author PT. Abadi Singapay Indonesia
  */
interface CacheInterface
{
    /**
     * Retrieve item from cache storage
     * 
     * Attempts to fetch a cached value using the specified key. Returns the
     * cached value if found and valid, or null if the key does not exist
     * or the cached item has expired. Implementations should automatically
     * handle TTL expiration and return null for expired items.
     * 
     * @param string $key Unique identifier for the cached item
     * 
     * @return mixed Cached value if found and valid, null otherwise
     */
    public function get($key);

    /**
     * Store item in cache storage
     * 
     * Persists a value in the cache with the specified key and optional
     * time-to-live (TTL). If TTL is provided, the cached item should
     * automatically expire after the specified number of seconds. If TTL
     * is null, the implementation may use a default expiration or store
     * the item indefinitely based on its configuration.
     * 
     * @param string $key Unique identifier for the cached item
     * @param mixed $value Data to be cached (must be serializable)
     * @param int|null $ttl Time-to-live in seconds, or null for default/indefinite
     * 
     * @return bool True if the item was successfully stored, false otherwise
     */
    public function set($key, $value, $ttl = null);

    /**
     * Remove item from cache storage
     * 
     * Explicitly removes a cached item identified by the specified key.
     * This operation should succeed regardless of whether the key exists
     * in the cache, providing idempotent behavior for cache invalidation
     * scenarios.
     * 
     * @param string $key Unique identifier for the cached item to remove
     * 
     * @return bool True if the item was removed or did not exist, false on failure
     */
    public function delete($key);

    /**
     * Clear entire cache storage
     * 
     * Removes all items from the cache storage, effectively performing
     * a complete cache reset. This operation is useful for cache invalidation
     * during deployment, configuration changes, or debugging scenarios.
     * 
     * @return bool True if the cache was successfully cleared, false otherwise
     */
    public function clear();

    /**
     * Check for item existence in cache
     * 
     * Verifies whether a cached item exists and is valid for the specified key.
     * This method should check both the existence of the key and the validity
     * of any TTL expiration, returning false for expired items.
     * 
     * @param string $key Unique identifier for the cached item to check
     * 
     * @return bool True if the item exists and is valid, false otherwise
     */
    public function has($key);
}
