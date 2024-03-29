<?php
namespace TriTan\Common\User;

use TriTan\Common\Container as c;
use TriTan\Common\Context\HelperContext;
use TriTan\Interfaces\User\UserInterface;
use TriTan\Database\Database;
use TriTan\Common\Acl\RoleRepository;
use TriTan\Common\Acl\RoleMapper;

/**
 * User Domain
 *
 * Inspired by WordPress' user class.
 *
 * @license GPLv3
 *
 * @since 1.0.0
 * @package Qubus CMS
 * @author Joshua Parker <josh@joshuaparker.blog>
 */
final class User implements UserInterface
{
    /**
     * User user_id.
     *
     * @since 1.0.0
     * @var int
     */
    private $user_id;

    /**
     * User username/login.
     *
     * @since 1.0.0
     * @var string
     */
    private $user_login;

    /**
     * User first name.
     *
     * @since 1.0.0
     * @var string
     */
    private $user_fname;

    /**
     * User last name.
     *
     * @since 1.0.0
     * @var string
     */
    private $user_lname;

    /**
     * User email.
     *
     * @since 1.0.0
     * @var string
     */
    private $user_email;

    /**
     * User password.
     *
     * @since 1.0.0
     * @var string
     */
    private $user_pass;

    /**
     * User url.
     *
     * @since 1.0.0
     * @var string
     */
    private $user_url;

    /**
     * User timezone.
     *
     * @since 1.0.0
     * @var string
     */
    private $user_timezone;

    /**
     * User date format.
     *
     * @since 1.0.0
     * @var string
     */
    private $user_date_format;

    /**
     * User time format.
     *
     * @since 1.0.0
     * @var string
     */
    private $user_time_format;

    /**
     * User locale.
     *
     * @since 1.0.0
     * @var string
     */
    private $user_locale;

    /**
     * Person who created user.
     *
     * @since 1.0.0
     * @var int
     */
    private $user_addedby;

    /**
     * User registration datetime.
     *
     * @since 1.0.0
     * @var string
     */
    private $user_registered;

    /**
     * User modified datetime.
     *
     * @since 1.0.0
     * @var string
     */
    private $user_modified;

    /**
     * User activation key.
     *
     * @since 1.0.0
     * @var string
     */
    private $user_activation_key;

    public function getId(): int
    {
        return $this->user_id;
    }

    public function setId(int $id)
    {
        return $this->user_id = $id;
    }

    public function getLogin()
    {
        return $this->user_login;
    }

    public function setLogin(string $login)
    {
        return $this->user_login = $login;
    }

    public function getFname()
    {
        return $this->user_fname;
    }

    public function setFname(string $fname)
    {
        return $this->user_fname = $fname;
    }

    public function getLname()
    {
        return $this->user_lname;
    }

    public function setLname(string $lname)
    {
        return $this->user_lname = $lname;
    }

    public function getEmail()
    {
        return $this->user_email;
    }

    public function setEmail(string $email)
    {
        return $this->user_email = $email;
    }

    public function getPassword()
    {
        return $this->user_pass;
    }

    public function setPassword(string $password)
    {
        return $this->user_pass = $password;
    }

    public function getUrl()
    {
        return $this->user_url;
    }

    public function setUrl(string $url)
    {
        return $this->user_url = $url;
    }

    public function getTimezone()
    {
        return $this->user_timezone;
    }

    public function setTimezone(string $timezone)
    {
        return $this->user_timezone = $timezone;
    }

    public function getDateFormat()
    {
        return $this->user_date_format;
    }

    public function setDateFormat(string $date_format)
    {
        return $this->user_date_format = $date_format;
    }

    public function getTimeFormat()
    {
        return $this->user_time_format;
    }

    public function setTimeFormat(string $time_format)
    {
        return $this->user_time_format = $time_format;
    }

    public function getLocale()
    {
        return $this->user_locale;
    }

    public function setLocale(string $locale)
    {
        return $this->user_locale = $locale;
    }

    public function getAddedBy(): int
    {
        return $this->user_addedby;
    }

    public function setAddedBy(int $addedby)
    {
        return $this->user_addedby = $addedby;
    }

    public function getRegistered()
    {
        return $this->user_registered;
    }

    public function setRegistered(string $registered)
    {
        return $this->user_registered = $registered;
    }

    public function getModified()
    {
        return $this->user_modified;
    }

    public function setModified(string $modified)
    {
        return $this->user_modified = $modified;
    }

    public function getActivationKey()
    {
        return $this->user_activation_key;
    }

    public function setActivationKey($activationkey = '')
    {
        return $this->user_activation_key = $activationkey;
    }

    /**
     * Magic method for checking the existence of a certain custom field.
     *
     * @since 1.0.0
     * @param string $key User meta key to check if set.
     * @return bool Whether the given user meta key is set.
     */
    public function __isset($key)
    {
        return c::getInstance()->get('meta')->exists('user', $this->user_id, c::getInstance()->get('tbl_prefix') . $key);
    }

    /**
     * Magic method for accessing custom fields.
     *
     * @since 1.0.0
     * @param string $key User meta key to retrieve.
     * @return mixed Value of the given user meta key (if set). If `$key` is 'id', the user ID.
     */
    public function __get($key)
    {
        $value = c::getInstance()->get('usermeta')->read($this->user_id, c::getInstance()->get('tbl_prefix') . $key, true);
        return c::getInstance()->get('context')->obj['escape']->html($value);
    }

    /**
     * Magic method for setting custom user fields.
     *
     * This method does not update custom fields in the user document. It only stores
     * the value on the User instance.
     *
     * @since 1.0.0
     * @param string $key   User meta key.
     * @param mixed  $value User meta value.
     */
    public function __set($key, $value)
    {
        if ('id' == $key || 'ID' == $key) {
            $this->setId((int) $value);
            return;
        }

        $this->{$key} = $value;
    }

    /**
     * Magic method for unsetting a certain custom field.
     *
     * @since 1.0.0
     * @param string $key User meta key to unset.
     */
    public function __unset($key)
    {
        if (isset($this->{$key})) {
            unset($this->{$key});
        }
    }

    /**
     * Retrieve the value of a property or meta key.
     *
     * Retrieves from the users and usermeta table.
     *
     * @since 1.0.0
     * @param string $key Property
     * @return mixed
     */
    public function get($key)
    {
        return $this->__get($key);
    }

    /**
     * Determine whether a property or meta key is set
     *
     * Consults the users and usermeta tables.
     *
     * @since 1.0.0
     * @param string $key Property
     * @return bool
     */
    public function hasProp($key)
    {
        return $this->__isset($key);
    }

    /**
     * Return an array representation.
     *
     * @since 1.0.0
     * @return array Array representation.
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    public function setRole(string $role)
    {
        $old_role = get_usermeta($this->getId(), c::getInstance()->get('tbl_prefix') . 'role', true);

        if (is_numeric($role)) {
            $message = 'Invalid role. Must use role_key (super, admin, editor, etc.) and not role_id.';
            _incorrectly_called(__FUNCTION__, $message, '1.0.0');
            return;
        }

        $new_role = (
            new RoleRepository(
                new RoleMapper(
                    new Database(c::getInstance()->get('connect')),
                    new HelperContext()
                )
            )
        )->findIdByKey($role);

        update_usermeta($this->getId(), c::getInstance()->get('tbl_prefix') . 'role', $new_role, $old_role);

        /**
         * Fires after the user's role has been added/changed.
         *
         * @since 1.0.0
         * @param int   $user_id    The user id.
         * @param int   $new_role   The new role.
         * @param int   $old_role   The user's previous role.
         */
        c::getInstance()->get('context')->obj['hook']->doAction('set_user_role', $this->getId(), $new_role, $old_role);
    }
}
