<?php
use Qubus\Hooks\ActionFilterHook;

/**
 * Qubus CMS Link Functions
 *
 * @license GPLv3
 *
 * @since 1.0.0
 * @package Qubus CMS
 * @author Joshua Parker <josh@joshuaparker.blog>
 */

/**
 * Sets the scheme for a URL.
 *
 * @file app/functions/link.php
 *
 * @since 1.0.0
 * @param string      $url    Absolute URL that includes a scheme
 * @param string|null $scheme Optional. Scheme to give $url. Currently 'http', 'https', 'login',
 *                            'admin', 'relative', 'rest' or null. Default null.
 * @return string $url URL with chosen scheme.
 */
function set_url_scheme($url, $scheme = null)
{
    $orig_scheme = $scheme;

    if (!$scheme) {
        $scheme = is_ssl() ? 'https' : 'http';
    } elseif ($scheme === 'admin' || $scheme === 'login') {
        $scheme = is_ssl() ? 'https' : 'http';
    } elseif ($scheme !== 'http' && $scheme !== 'https' && $scheme !== 'relative') {
        $scheme = is_ssl() ? 'https' : 'http';
    }

    $url = trim($url);
    if (substr($url, 0, 2) === '//') {
        $url = 'http:' . $url;
    }

    if ('relative' == $scheme) {
        $url = ltrim(preg_replace('#^\w+://[^/]*#', '', $url));
        if ($url !== '' && $url[0] === '/') {
            $url = '/' . ltrim($url, "/ \t\n\r\0\x0B");
        }
    } else {
        $url = preg_replace('#^\w+://#', $scheme . '://', $url);
    }

    /**
     * Filters the resulting URL after setting the scheme.
     *
     * @since 1.0.0
     *
     * @param string      $url         The complete URL including scheme and path.
     * @param string      $scheme      Scheme applied to the URL. One of 'http', 'https', or 'relative'.
     * @param string|null $orig_scheme Scheme requested for the URL. One of 'http', 'https', 'login',
     *                                 'admin', 'relative', or null.
     */
    return ActionFilterHook::getInstance()->applyFilter('set_url_scheme', $url, $scheme, $orig_scheme);
}

/**
 * Returns the url for a given site.
 *
 * Returns 'https' if `is_ssl()` evaluates to true and 'http' otherwise. If `$scheme` is
 * 'http' or 'https', `is_ssl(`) is overridden.
 *
 * @file app/functions/link.php
 *
 * @since 1.0.0
 * @param string $path   Optional. Route relative to the site url. Default '/'.
 * @param string $scheme Optional. Scheme to give the site URL context. Accepts
 *                       'http', 'https', 'login', 'admin', or 'relative'.
 *                       Default null.
 * @return string Site url link.
 */
function ttcms_site_url($path = '', $scheme = null)
{
    $uri = url('/');
    $url = set_url_scheme($uri, $scheme);

    if ($path && is_string($path)) {
        $url .= ltrim($path, '/');
    }

    /**
     * Filters the site URL.
     *
     * @since 1.0.0
     *
     * @param string $url         The site url including scheme and path.
     * @param string $path        Route relative to the site url. Blank string if no path is specified.
     * @param string|null $scheme Scheme to give the site url context. Accepts 'http', 'https', 'login',
     *                            'admin', 'relative' or null.
     */
    return ActionFilterHook::getInstance()->applyFilter('site_url', $url, $path, $scheme);
}

/**
 * Returns the url to the admin area for a given site.
 *
 * @file app/functions/link.php
 *
 * @since 1.0.0
 * @param string $path   Optional. Path relative to the admin url. Default empty.
 * @param string $scheme Optional. The scheme to use. Accepts 'http' or 'https',
 *                       to force those schemes. Default 'admin'.
 * @return string Admin url link with optional path appended.
 */
function ttcms_admin_url($path = '', $scheme = 'admin')
{
    $url = ttcms_site_url('admin/', $scheme);

    if ($path && is_string($path)) {
        $url .= ltrim($path, '/');
    }

    $esc_url = esc_url($url);

    /**
     * Filters the admin area url.
     *
     * @since 1.0.0
     *
     * @param string $esc_url The complete admin area url including scheme and path after escaped.
     * @param string $url     The complete admin area url including scheme and path before escaped.
     * @param string $path Path relative to the admin area url. Blank string if no path is specified.
     */
    return ActionFilterHook::getInstance()->applyFilter('admin_url', $esc_url, $url, $path);
}

/**
 * Returns the url for a given site where the front end is accessible.
 *
 * The protocol will be 'https' if `is_ssl()` evaluates to true; If `$scheme` is
 * 'http' or 'https', `is_ssl()` is overridden.
 *
 * @file app/functions/link.php
 *
 * @since 1.0.0
 * @param string      $path   Optional. Path relative to the home url. Default empty.
 * @param string|null $scheme Optional. Scheme to give the home URL context. Accepts
 *                            'http', 'https', 'relative', or null. Default null.
 * @return string Home url link with optional path appended.
 */
function ttcms_home_url($path = '', $scheme = null)
{
    $orig_scheme = $scheme;
    $uri = url('/');

    if (! in_array($scheme, [ 'http', 'https', 'relative' ])) {
        if (is_ssl() && ! is_admin() && ! is_login()) {
            $scheme = 'https';
        } else {
            $scheme = parse_url($uri, PHP_URL_SCHEME);
        }
    }

    $url = set_url_scheme($uri, $scheme);

    if ($path && is_string($path)) {
        $url .= ltrim($path, '/');
    }

    $esc_url = esc_url($url);

    /**
     * Filters the home URL.
     *
     * @since 1.0.0
     *
     * @param string      $esc_url The the escaped home url.
     * @param string      $url     The home url before it was escaped.
     * @param string      $path    Route relative to the site url. Blank string if no path is specified.
     * @param string|null $scheme  Scheme to give the site url context. Accepts 'http', 'https',
     *                             'relative' or null.
     */
    return ActionFilterHook::getInstance()->applyFilter('home_url', $esc_url, $url, $path, $orig_scheme);
}

/**
 * Returns the login url for a given site.
 *
 * @file app/functions/link.php
 *
 * @since 1.0.0
 * @param string $redirect Path to redirect to on log in.
 * @param string $path     Optional. Path relative to the login url. Default empty.
 * @param string|null $scheme  Optional. Scheme to give the logout URL context. Accepts
 *                             'http', 'https', 'relative', or null. Default 'login'.
 * @return string Returns the login url.
 */
function ttcms_login_url($redirect = '', $path = '', $scheme = 'login')
{
    $url = ttcms_site_url('login/', $scheme);

    if ($path && is_string($path)) {
        $url .= ltrim($path, '/');
    }

    if (!empty($redirect)) {
        $login_url = add_query_arg('redirect_to', $redirect, $url);
    }

    /**
     * Validates the redirect url.
     *
     * @since 1.0.0
     */
    if (!empty($redirect) && !validate_url($redirect)) {
        $login_url = $url;
    }

    /**
     * Last check, and escape again just in case.
     *
     * @since 1.0.0
     */
    if (!empty($redirect)) {
        $login_url = esc_url($login_url);
    } else {
        $login_url = esc_url($url);
    }

    /**
     * Filters the login URL.
     *
     * @since 1.0.0
     *
     * @param string $login_url    The login URL. Not HTML-encoded.
     * @param string $redirect     The path to redirect to on login, if supplied.
     * @param string $path         Route relative to the login url. Blank string if no path is specified.
     * @param string|null $scheme  Scheme to give the login url context. Accepts 'http', 'https',
     *                             'relative' or null.
     */
    return ActionFilterHook::getInstance()->applyFilter('login_url', $login_url, $redirect, $path, $scheme);
}

/**
 * Returns the login url for a given site.
 *
 * @file app/functions/link.php
 *
 * @since 1.0.0
 * @param string $redirect Path to redirect to on logout.
 * @param string $path     Optional. Path relative to the logout url. Default empty.
 * @param string|null $scheme  Optional. Scheme to give the logout URL context. Accepts
 *                             'http', 'https', 'relative', or null. Default 'logout'.
 * @return string Returns the logout url.
 */
function ttcms_logout_url($redirect = '', $path = '', $scheme = 'logout')
{
    $url = ttcms_site_url('logout/', $scheme);

    if ($path && is_string($path)) {
        $url .= ltrim($path, '/');
    }

    if (!empty($redirect)) {
        $logout_url = add_query_arg('redirect_to', $redirect, $url);
    }

    /**
     * Validates the redirect url.
     *
     * @since 1.0.0
     */
    if (!empty($redirect) && !validate_url($redirect)) {
        $logout_url = $url;
    }

    /**
     * Last check, and escape again just in case.
     *
     * @since 1.0.0
     */
    if (!empty($redirect)) {
        $logout_url = esc_url($logout_url);
    } else {
        $logout_url = esc_url($url);
    }

    /**
     * Filters the logout URL.
     *
     * @since 1.0.0
     *
     * @param string $logout_url   The logout URL. Not HTML-encoded.
     * @param string $redirect     The path to redirect to on logout, if supplied.
     * @param string $path         Route relative to the logout url. Blank string if no path is specified.
     * @param string|null $scheme  Scheme to give the logout url context. Accepts 'http', 'https',
     *                             'relative' or null.
     */
    return ActionFilterHook::getInstance()->applyFilter('logout_url', $logout_url, $redirect, $path, $scheme);
}

/**
 * Returns the url for a given site.
 *
 * Returns 'https' if `is_ssl()` evaluates to true and 'http' otherwise. If `$scheme` is
 * 'http' or 'https', `is_ssl()` is overridden.
 *
 * @file app/functions/link.php
 *
 * @since 1.0.0
 * @param string $path    Optional. Route relative to the site url. Default '/'.
 * @param string $scheme  Optional. Scheme to give the site URL context. Accepts
 *                        'http', 'https', 'login', 'admin', or 'relative'.
 *                        Default null.
 * @return string Site url link.
 */
function site_url($path = '', $scheme = null)
{
    return esc_url(ttcms_site_url($path, $scheme));
}

/**
 * Returns the url to the admin area for a given site.
 *
 * @file app/functions/link.php
 *
 * @since 1.0.0
 * @param string $path    Optional. Path relative to the admin url. Default empty.
 * @param string $scheme  Optional. The scheme to use. Accepts 'http' or 'https',
 *                        to force those schemes. Default 'admin'.
 * @return string Admin url link with optional path appended.
 */
function admin_url($path = '', $scheme = 'admin')
{
    return ttcms_admin_url($path, $scheme);
}

/**
 * Returns the url for a given site where the front end is accessible.
 *
 * The protocol will be 'https' if `is_ssl()` evaluates to true; If `$scheme` is
 * 'http' or 'https', `is_ssl()` is overridden.
 *
 * @file app/functions/link.php
 *
 * @since 1.0.0
 * @param string      $path    Optional. Path relative to the home url. Default empty.
 * @param string|null $scheme  Optional. Scheme to give the home URL context. Accepts
 *                             'http', 'https', 'relative', or null. Default null.
 * @return string Home url link with optional path appended.
 */
function home_url($path = '', $scheme = null)
{
    return ttcms_home_url($path, $scheme);
}

/**
 * Returns the login url for a given site.
 *
 * @file app/functions/link.php
 *
 * @since 1.0.0
 * @param string $redirect Path to redirect to on log in.
 * @param string $path     Optional. Path relative to the login url. Default empty.
 * @param string|null $scheme  Optional. Scheme to give the home URL context. Accepts
 *                             'http', 'https', 'relative', or null. Default 'login'.
 * @return string Returns the login url.
 */
function login_url($redirect = '', $path = '', $scheme = 'login')
{
    return ttcms_login_url($redirect, $path, $scheme);
}

/**
 * Returns the login url for a given site.
 *
 * @file app/functions/link.php
 *
 * @since 1.0.0
 * @param string $redirect Path to redirect to on logout.
 * @param string $path     Optional. Path relative to the logout url. Default empty.
 * @param string|null $scheme  Optional. Scheme to give the logout URL context. Accepts
 *                             'http', 'https', 'relative', or null. Default 'logout'.
 * @return string Returns the logout url.
 */
function logout_url($redirect = '', $path = '', $scheme = 'logout')
{
    return ttcms_logout_url($redirect, $path, $scheme);
}
