<?php
namespace TriTan\Interfaces\Cache;

interface CacheInterface
{
    /**
     * Adds data to the cache, if the cache key doesn't already exist.
     *
     * @since 1.0.0
     * @param int|string $key The cache key to use for retrieval later.
     * @param mixed $data The data to add to the cache.
     * @param string $namespace Optional. Where the cache contents are namespaced.
     * @param int $expire Optional. When the cache data should expire, in seconds.
     *                    Default: 3600 seconds = 1 hour / 60 minutes.
     * @return bool False if cache key already exists, true on success.
     */
    public function create($key, $data, $namespace, $expire);

    /**
     * Retrieves the cache contents from the cache by key and group.
     *
     * @since 1.0.0
     * @param int|string $key The key under which the cache contents are stored.
     * @param string $namespace Optional. Where the cache contents are namespaced.
     * @return bool|mixed False on failure to retrieve contents or the cache
     *                    contents on success.
     */
    public function read($key, $namespace);
    /**
     * Replaces the contents of the cache with new data.
     *
     * @since 1.0.0
     * @param int|string $key The key for the cache data that should be replaced.
     * @param mixed $data The new data to store in the cache.
     * @param string $namespace Optional. Where the cache contents are namespaced.
     * @param int $expire Optional. When to expire the cache contents, in seconds.
     *                    Default: 3600 seconds = 1 hour / 60 minutes.
     * @return bool False if original value does not exist, true if contents were replaced
     */
    public function update($key, $data, $namespace, $expire);
    /**
     * Removes the cache contents matching key and group.
     *
     * @since 1.0.0
     * @param int|string $key What the contents in the cache are called.
     * @param string $namespace Optional. Where the cache contents are namespaced.
     * @return bool True on successful removal, false on failure.
     */
    public function delete($key, $namespace);
    /**
     * Removes all cache items.
     *
     * @since 1.0.0
     * @return bool False on failure, true on success
     */
    public function flush();
    /**
     * Removes all cache items from a particular namespace.
     *
     * @since 1.0.0
     * @param string $value The namespace to delete from.
     * @return bool False on failure, true on success
     */
    public function flushNamespace($value);
    /**
     * Sets the data contents into the cache.
     *
     * @since 1.0.0
     * @param int|string $key Unique key of the cache file.
     * @param mixed $data Data that should be cached.
     * @param string $namespace Optional. Where the cache contents are namespaced. Default: 'default'.
     * @param int $expire Optional. When to expire the cache contents, in seconds.
     *                    Default: 3600 seconds = 1 hour / 60 minutes.
     * @return bool Returns true if the cache was set and false otherwise.
     */
    public function set($key, $data, $namespace, $expire);
    /**
     * Returns the stats of the cache.
     *
     * Gives the cache hits, cache misses and cache uptime.
     *
     * @since 1.0.0
     */
    public function getStats();
    /**
     * Increments numeric cache item's value.
     *
     * @since 1.0.0
     * @param int|string $key The cache key to increment
     * @param int $offset Optional. The amount by which to increment the item's value. Default: 1.
     * @param string $namespace Optional. The namespace the key is in.
     * @return false|int False on failure, the item's new value on success.
     */
    public function increment($key, $offset, $namespace);
    /**
     * Decrements numeric cache item's value.
     *
     * @since 1.0.0
     * @param int|string $key The cache key to decrement.
     * @param int $offset Optional. The amount by which to decrement the item's value. Default: 1.
     * @param string $namespace Optional. The namespace the key is in.
     * @return false|int False on failure, the item's new value on success.
     */
    public function decrement($key, $offset, $namespace);
}
