import { CacheInterface } from "./CacheInterface.js";

/**
 * RedisCache - Redis-Based Cache Implementation
 *
 * A concrete implementation of the CacheInterface that utilizes Redis as
 * the underlying cache storage backend. This implementation provides
 * distributed caching capabilities with persistence and advanced features
 * suitable for production environments and multi-instance deployments.
 *
 * This cache implementation is ideal for production scenarios requiring
 * high performance, data persistence across application restarts, and
 * shared cache access across multiple application instances.
 *
 * @extends CacheInterface
 * @package '@singapay/payment-gateway'
 * @author PT. Abadi Singapay Indonesia
 */
export class RedisCache extends CacheInterface {
  /**
   * Creates a new RedisCache instance
   *
   * Initializes the Redis client connection and configures the key prefix
   * for namespace isolation. All cache operations will automatically apply
   * the specified prefix to prevent key collisions with other applications
   * using the same Redis instance.
   *
   * @param {object} redisClient Configured Redis client instance (ioredis, node-redis, etc.)
   * @param {string} prefix Key prefix for namespace isolation (default: "singapay_")
   *
   * @example
   * const Redis = require('ioredis');
   * const redisClient = new Redis();
   * const cache = new RedisCache(redisClient, 'singapay_');
   */
  constructor(redisClient, prefix = "singapay_") {
    super();
    /**
     * @private
     * @type {object}
     */
    this.redis = redisClient;
    /**
     * @private
     * @type {string}
     */
    this.prefix = prefix;
  }

  /**
   * Retrieve item from Redis cache
   *
   * Attempts to fetch a cached value using the specified key from Redis.
   * Automatically handles JSON deserialization and returns null if the
   * key does not exist. Redis TTL expiration is handled automatically
   * by the Redis server.
   *
   * @param {string} key Unique identifier for the cached item
   * @returns {Promise<*>} Promise resolving to cached value if found, null otherwise
   *
   * @example
   * const userData = await cache.get('user:123');
   * if (userData) {
   *   console.log('Retrieved from Redis:', userData);
   * }
   */
  async get(key) {
    const value = await this.redis.get(this.prefix + key);
    if (value === null) {
      return null;
    }
    return JSON.parse(value);
  }

  /**
   * Store item in Redis cache
   *
   * Persists a value in Redis with the specified key and optional time-to-live (TTL).
   * Values are automatically serialized to JSON format for storage. If TTL is provided,
   * the item will automatically expire after the specified seconds using Redis' built-in
   * expiration mechanism.
   *
   * @param {string} key Unique identifier for the cached item
   * @param {*} value Data to be cached (must be JSON-serializable)
   * @param {number|null} ttl Time-to-live in seconds, or null for no expiration
   * @returns {Promise<boolean>} Promise resolving to true if the item was successfully stored
   *
   * @throws {Error} If JSON serialization fails or Redis operation fails
   *
   * @example
   * // Store with 1 hour TTL
   * await cache.set('session:abc', sessionData, 3600);
   *
   * // Store indefinitely
   * await cache.set('configuration', configData);
   */
  async set(key, value, ttl = null) {
    const serialized = JSON.stringify(value);
    const prefixedKey = this.prefix + key;

    if (ttl !== null) {
      await this.redis.setex(prefixedKey, ttl, serialized);
    } else {
      await this.redis.set(prefixedKey, serialized);
    }

    return true;
  }

  /**
   * Remove item from Redis cache
   *
   * Explicitly removes a cached item identified by the specified key from Redis.
   * Returns true if the key was found and deleted, false if the key didn't exist.
   *
   * @param {string} key Unique identifier for the cached item to remove
   * @returns {Promise<boolean>} Promise resolving to true if item was deleted, false if not found
   *
   * @example
   * const deleted = await cache.delete('user:123');
   * if (deleted) {
   *   console.log('Successfully removed from Redis');
   * }
   */
  async delete(key) {
    const result = await this.redis.del(this.prefix + key);
    return result > 0;
  }

  /**
   * Clear all SingaPay items from Redis cache
   *
   * Removes all cache items that match the configured key prefix from Redis.
   * This operation uses Redis KEYS command for pattern matching followed by
   * DEL command for bulk deletion. Use with caution in production environments
   * with large datasets.
   *
   * @returns {Promise<boolean>} Promise resolving to true if operation completed successfully
   *
   * @warning The KEYS command may block Redis server with large datasets
   *
   * @example
   * await cache.clear();
   * console.log('All SingaPay cache items cleared');
   */
  async clear() {
    const keys = await this.redis.keys(this.prefix + "*");
    if (keys.length === 0) {
      return true;
    }
    const result = await this.redis.del(...keys);
    return result > 0;
  }

  /**
   * Check for item existence in Redis cache
   *
   * Verifies whether a cached item exists for the specified key in Redis.
   * This method checks both key existence and validity (non-expired state)
   * using Redis' built-in expiration mechanism.
   *
   * @param {string} key Unique identifier for the cached item to check
   * @returns {Promise<boolean>} Promise resolving to true if the item exists and is valid
   *
   * @example
   * if (await cache.has('user:123')) {
   *   console.log('Item exists in Redis cache');
   * }
   */
  async has(key) {
    const result = await this.redis.exists(this.prefix + key);
    return result > 0;
  }
}
