<?php
namespace TriTan\Common\User;

use TriTan\Interfaces\Cache\ObjectCacheInterface;
use TriTan\Interfaces\User\UserCacheInterface;
use TriTan\Common\User\User;
use Qubus\Hooks\Interfaces\ActionFilterHookInterface;

final class UserCache implements UserCacheInterface
{
    protected $cache;

    protected $hook;

    public function __construct(ObjectCacheInterface $cache, ActionFilterHookInterface $hook)
    {
        $this->cache = $cache;
        $this->hook = $hook;
    }

    /**
     * Update user caches.
     *
     * @since 1.0.0
     * @param int|object $user User object or id to be cached.
     * @return bool|null Returns false on failure.
     */
    public function update($user)
    {
        if (empty($user)) {
            return;
        }

        $_user = $user->toArray();

        $this->cache->create((int) $user->getId(), $_user, 'users');
        $this->cache->create($user->getLogin(), (int) $user->getId(), 'userlogins');
        $this->cache->create($user->getEmail(), (int) $user->getId(), 'useremail');
    }

    /**
     * Clean user caches.
     *
     * Uses `clean_user_cache` action.
     *
     * @since 1.0.0
     * @param int|object $user User object or id to be cleaned from the cache.
     */
    public function clean($user)
    {
        if (empty($user)) {
            return;
        }

        $user_id = $user;
        $user = get_userdata($user_id);
        if (!$user) {
            if (!is_numeric($user_id)) {
                return;
            }

            // Make sure a User object exists even when the user has been deleted.
            $user = new User();
            $user->setId($user_id);
            $user->setLogin(null);
            $user->setEmail(null);
        }

        $user_id = $user->getId();

        $this->cache->delete((int) $user->getId(), 'users');
        $this->cache->delete($user->getLogin(), 'userlogins');
        $this->cache->delete($user->getEmail(), 'useremail');
        $this->cache->delete((int) $user->getId(), 'usermeta');

        /**
         * Fires immediately after the given user's cache is cleaned.
         *
         * @since 1.0.0
         * @param int   $user_id User id.
         * @param User  $user    User object.
         */
        $this->hook->doAction('clean_user_cache', (int) $user_id, $user);
    }
}
