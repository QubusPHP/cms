<?php
namespace TriTan\Interfaces\Posttype;

interface PosttypeCacheInterface
{
    /**
     * Update posttype caches.
     *
     * @since 1.0.0
     * @param Posttype|null $posttype Posttype to be cached.
     */
    public function update($posttype);

    /**
     * Clean posttype caches.
     *
     * @since 1.0.0
     * @param Posttype|int $posttype Posttype or posttype id to be cleaned from the cache.
     */
    public function clean($posttype);
}
