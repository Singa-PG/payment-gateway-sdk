<?php

namespace SingaPay\Cache;

/**
 * SingaPay Array Cache Implementation
 * 
 * In-memory cache implementation using PHP arrays for storage. This cache
 * provider offers fast, process-local caching suitable for development,
 * testing, and single-process environments. Data stored in ArrayCache is
 * volatile and exists only for the duration of the current PHP process.
 * 
 * This implementation provides automatic TTL expiration handling and
 * efficient in-memory operations. While suitable for development and
 * testing scenarios, production environments should consider persistent
 * cache implementations like Redis or Memcached for distributed caching
 * and data persistence across processes.
 * 
 * @package SingaPay\Cache
 * @author PT. Abadi Singapay Indonesia
  */
class ArrayCache implements CacheInterface
{
    /**
     * @var array Key-value storage for cached items
     */
    private $storage = [];

    /**
     * @var array Expiration timestamps for TTL-managed items
     */
    private $expirations = [];

    /**
     * Retrieve item from in-memory cache
     * 
     * Attempts to fetch a cached value from the in-memory storage array.
     * Automatically checks for TTL expiration and returns null for expired
     * items. Provides fast, process-local cache retrieval without external
     * dependencies.
     * 
     * @param string $key Unique identifier for the cached item
     * 
     * @return mixed Cached value if found and not expired, null otherwise
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            return null;
        }

        return $this->storage[$key];
    }

    /**
     * Store item in in-memory cache
     * 
     * Persists a value in the process memory with the specified key and
     * optional time-to-live. If TTL is provided, calculates the expiration
     * timestamp based on current time. Items without TTL remain in cache
     * until explicitly deleted or the process ends.
     * 
     * @param string $key Unique identifier for the cached item
     * @param mixed $value Data to be cached (any PHP data type)
     * @param int|null $ttl Time-to-live in seconds, or null for no expiration
     * 
     * @return bool Always returns true for successful in-memory storage
     */
    public function set($key, $value, $ttl = null)
    {
        $this->storage[$key] = $value;

        if ($ttl !== null) {
            $this->expirations[$key] = time() + $ttl;
        } else {
            unset($this->expirations[$key]);
        }

        return true;
    }

    /**
     * Remove item from in-memory cache
     * 
     * Deletes the specified key from both the storage and expiration
     * tracking arrays. This operation is immediate and always successful
     * for in-memory storage, providing reliable cache invalidation.
     * 
     * @param string $key Unique identifier for the cached item to remove
     * 
     * @return bool Always returns true for successful in-memory deletion
     */
    public function delete($key)
    {
        unset($this->storage[$key]);
        unset($this->expirations[$key]);

        return true;
    }

    /**
     * Clear all items from in-memory cache
     * 
     * Completely resets the cache by clearing both the storage and
     * expiration tracking arrays. This provides immediate and complete
     * cache invalidation for all stored items.
     * 
     * @return bool Always returns true for successful cache clearance
     */
    public function clear()
    {
        $this->storage = [];
        $this->expirations = [];

        return true;
    }

    /**
     * Check for item existence and validity in cache
     * 
     * Verifies whether a cached item exists and has not expired. Automatically
     * removes expired items from storage during the check process, ensuring
     * cache hygiene and preventing memory leaks from expired entries.
     * 
     * @param string $key Unique identifier for the cached item to check
     * 
     * @return bool True if item exists and is not expired, false otherwise
     */
    public function has($key)
    {
        if (!isset($this->storage[$key])) {
            return false;
        }

        if (isset($this->expirations[$key]) && time() > $this->expirations[$key]) {
            $this->delete($key);
            return false;
        }

        return true;
    }
}
