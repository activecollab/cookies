<?php

namespace ActiveCollab\Cookies;

/**
 * @package ActiveCollab\Cookies
 */
interface CookiesInterface
{
    /**
     * Return true if cookie with the given name exists
     *
     * @param  string  $name
     * @return boolean
     */
    public function exists($name);

    /**
     * Read a cookie with the given name
     *
     * @param  string $name
     * @return mixed
     */
    public function get($name);

    /**
     * @param string       $name
     * @param mixed        $value
     * @param integer|null $ttl
     * @param bool|true    $http_only
     */
    public function set($name, $value, $ttl = null, $http_only = true);

    /**
     * @param string $name
     */
    public function remove($name);

    /**
     * Return cookie domain
     *
     * @return string
     */
    public function getDomain();

    /**
     * Set cookie domain
     *
     * @param  string $domain
     * @return $this
     */
    public function &domain($domain);

    /**
     * @return $this
     */
    public function getPath();

    /**
     * Set cookie path
     *
     * @param  string $path
     * @return $this
     */
    public function &path($path);

    /**
     * Return true if cookie should be transfmitted only via secure connection
     *
     * @return boolean
     */
    public function getSecure();

    /**
     * Set whether cookies should be transmitted only via secure connection
     *
     * @param  boolean $secure
     * @return $this
     */
    public function &secure($secure);

    /**
     * Return variable name prefix
     *
     * @return string
     */
    public function getPrefix();

    /**
     * Set variable name prefix
     *
     * @param  string $prefix
     * @return $this
     */
    public function &prefix($prefix);

    /**
     * Configure cookie domain, secure flag and domain from URL
     *
     * @param  string $url
     * @return $this
     */
    public function &configureFromUrl($url);
}
