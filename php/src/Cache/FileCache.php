<?php

namespace SingaPay\Cache;

/**
 * SingaPay File Cache Implementation
 * 
 * File system-based cache implementation that persists cached data to disk.
 * This cache provider offers persistent storage across PHP processes and
 * server restarts, making it suitable for production environments where
 * in-memory caching is insufficient but external cache systems are unavailable.
 * 
 * The implementation uses PHP serialization for data storage and provides
 * automatic TTL expiration management. Cache files are stored in a dedicated
 * directory with hashed filenames for security and filesystem compatibility.
 * 
 * @package SingaPay\Cache
 * @author PT. Abadi Singapay Indonesia
  */
class FileCache implements CacheInterface
{
    /**
     * @var string Directory path for cache file storage
     */
    private $cacheDir;

    /**
     * Initialize File Cache
     * 
     * Constructs a new file cache instance with the specified cache directory.
     * If no directory is provided, uses the system temporary directory with
     * a 'singapay_cache' subdirectory. Automatically creates the cache
     * directory if it does not exist.
     * 
     * @param string|null $cacheDir Custom cache directory path, or null for default
     */
    public function __construct($cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?? sys_get_temp_dir() . '/singapay_cache';

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Retrieve item from file cache
     * 
     * Attempts to fetch a cached value from the file system storage.
     * Automatically checks for TTL expiration and returns null for expired
     * items. Uses PHP unserialization to restore the original data structure
     * from the cached file.
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

        $filename = $this->getFilename($key);
        $data = unserialize(file_get_contents($filename));

        return $data['value'];
    }

    /**
     * Store item in file cache
     * 
     * Persists a value to the file system with the specified key and
     * optional time-to-live. Serializes the data along with expiration
     * information and writes it to a dedicated cache file. If TTL is
     * provided, calculates the expiration timestamp based on current time.
     * 
     * @param string $key Unique identifier for the cached item
     * @param mixed $value Data to be cached (must be serializable)
     * @param int|null $ttl Time-to-live in seconds, or null for no expiration
     * 
     * @return bool True if the item was successfully written to disk, false on failure
     */
    public function set($key, $value, $ttl = null)
    {
        $filename = $this->getFilename($key);
        $expiry = $ttl !== null ? time() + $ttl : null;

        $data = [
            'value' => $value,
            'expiry' => $expiry
        ];

        return file_put_contents($filename, serialize($data)) !== false;
    }

    /**
     * Remove item from file cache
     * 
     * Deletes the cache file associated with the specified key from the
     * file system. Returns true if the file was successfully deleted or
     * did not exist, providing idempotent behavior for cache invalidation.
     * 
     * @param string $key Unique identifier for the cached item to remove
     * 
     * @return bool True if the file was deleted or did not exist, false on deletion failure
     */
    public function delete($key)
    {
        $filename = $this->getFilename($key);

        if (file_exists($filename)) {
            return unlink($filename);
        }

        return true;
    }

    /**
     * Clear all items from file cache
     * 
     * Removes all cache files from the cache directory, performing a complete
     * cache reset. This operation deletes all files with the .cache extension
     * in the cache directory while preserving the directory structure itself.
     * 
     * @return bool True if all cache files were successfully deleted
     */
    public function clear()
    {
        $files = glob($this->cacheDir . '/*.cache');

        if ($files === false) {
            return false;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }


    /**
     * Check for item existence and validity in file cache
     * 
     * Verifies whether a cached item exists in the file system and has not
     * expired. Automatically removes expired cache files during the check
     * process, ensuring disk space is reclaimed and cache hygiene is maintained.
     * 
     * @param string $key Unique identifier for the cached item to check
     * 
     * @return bool True if cache file exists and is not expired, false otherwise
     */
    public function has($key)
    {
        $filename = $this->getFilename($key);

        if (!file_exists($filename)) {
            return false;
        }

        $data = unserialize(file_get_contents($filename));

        if ($data['expiry'] !== null && time() > $data['expiry']) {
            $this->delete($key);
            return false;
        }

        return true;
    }

    /**
     * Clean up expired cache files
     * 
     * @return int Number of files cleaned
     */
    public function cleanup()
    {
        $files = glob($this->cacheDir . '/*.cache');
        $cleaned = 0;

        if ($files === false) {
            return 0;
        }

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $data = @unserialize(file_get_contents($file));

            if ($data === false) {
                continue;
            }

            if ($data['expiry'] !== null && time() > $data['expiry']) {
                unlink($file);
                $cleaned++;
            }
        }

        return $cleaned;
    }

    /**
     * Generate cache filename from key
     * 
     * Creates a secure filename for cache storage by hashing the cache key
     * using MD5. This ensures filesystem compatibility by avoiding special
     * characters and provides consistent filename generation across different
     * systems and cache key formats.
     * 
     * @param string $key Original cache key
     * 
     * @return string Full path to the cache file
     */
    private function getFilename($key)
    {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }
}
