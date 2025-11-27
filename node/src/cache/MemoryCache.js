import { CacheInterface } from "./CacheInterface.js";

/**
 * MemoryCache - In-Memory Cache Implementation
 *
 * A concrete implementation of the CacheInterface that stores cached items
 * in the application's memory using JavaScript Map objects. This implementation
 * provides fast in-process caching with automatic TTL expiration management.
 *
 * This cache implementation is ideal for development environments, testing,
 * or production scenarios where a simple in-memory cache suffices and data
 * persistence between application restarts is not required.
 *
 * @extends CacheInterface
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class MemoryCache extends CacheInterface {
  /**
   * Creates a new MemoryCache instance
   *
   * Initializes the underlying storage Map for cached values and a separate
   * Map for tracking expiration times. Both storage structures are automatically
   * created and managed internally.
   */
  constructor() {
    super();
    /**
     * @private
     * @type {Map<string, *>}
     */
    this.storage = new Map();
    /**
     * @private
     * @type {Map<string, number>}
     */
    this.expirations = new Map();
  }

  /**
   * Retrieve item from memory cache
   *
   * Attempts to fetch a cached value using the specified key. Automatically
   * checks for TTL expiration and returns null if the item has expired.
   * Expired items are automatically removed from cache during this check.
   *
   * @param {string} key Unique identifier for the cached item
   * @returns {Promise<*>} Promise resolving to cached value if found and valid, null otherwise
   *
   * @example
   * const value = await cache.get('user:123');
   * if (value) {
   *   console.log('Found cached value:', value);
   * }
   */
  async get(key) {
    if (!(await this.has(key))) {
      return null;
    }
    return this.storage.get(key);
  }

  /**
   * Store item in memory cache
   *
   * Persists a value in the memory cache with the specified key and optional
   * time-to-live (TTL). If TTL is provided, the item will automatically expire
   * after the specified number of seconds. If TTL is null, the item will be
   * stored indefinitely until explicitly removed or the application restarts.
   *
   * @param {string} key Unique identifier for the cached item
   * @param {*} value Data to be cached (any serializable JavaScript value)
   * @param {number|null} ttl Time-to-live in seconds, or null for indefinite storage
   * @returns {Promise<boolean>} Promise resolving to true if the item was successfully stored
   *
   * @example
   * // Store with 5 minute TTL
   * await cache.set('user:123', userData, 300);
   *
   * // Store indefinitely
   * await cache.set('config', appConfig);
   */
  async set(key, value, ttl = null) {
    this.storage.set(key, value);

    if (ttl !== null) {
      const expiryTime = Date.now() + ttl * 1000;
      this.expirations.set(key, expiryTime);
    } else {
      this.expirations.delete(key);
    }

    return true;
  }

  /**
   * Remove item from memory cache
   *
   * Explicitly removes a cached item identified by the specified key from
   * both the value storage and expiration tracking. This operation is
   * idempotent and will succeed even if the key doesn't exist.
   *
   * @param {string} key Unique identifier for the cached item to remove
   * @returns {Promise<boolean>} Promise resolving to true when removal is complete
   *
   * @example
   * await cache.delete('user:123');
   */
  async delete(key) {
    this.storage.delete(key);
    this.expirations.delete(key);
    return true;
  }

  /**
   * Clear entire memory cache
   *
   * Removes all items from both the value storage and expiration tracking,
   * effectively performing a complete cache reset. This operation is useful
   * for cache invalidation during deployment or testing scenarios.
   *
   * @returns {Promise<boolean>} Promise resolving to true when cache is cleared
   *
   * @example
   * await cache.clear();
   */
  async clear() {
    this.storage.clear();
    this.expirations.clear();
    return true;
  }

  /**
   * Check for item existence in memory cache
   *
   * Verifies whether a cached item exists and is valid for the specified key.
   * Automatically checks TTL expiration and removes expired items during the
   * check. Returns false for non-existent or expired items.
   *
   * @param {string} key Unique identifier for the cached item to check
   * @returns {Promise<boolean>} Promise resolving to true if the item exists and is valid
   *
   * @example
   * if (await cache.has('user:123')) {
   *   console.log('Item exists in cache');
   * }
   */
  async has(key) {
    if (!this.storage.has(key)) {
      return false;
    }

    if (this.expirations.has(key)) {
      const expiryTime = this.expirations.get(key);
      if (Date.now() > expiryTime) {
        await this.delete(key);
        return false;
      }
    }

    return true;
  }
}
