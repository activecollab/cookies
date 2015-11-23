<?php

namespace ActiveCollab\Cookies;

use ActiveCollab\Cookies\Adapter\AdapterInterface;

/**
 * @package ActiveCollab\Cookies
 */
interface CookiesInterface extends AdapterInterface
{
    /**
     * @return integer
     */
    public function getDefaultTtl();

    /**
     * Set default cookie TTL (time to live)
     *
     * @param  integer $value
     * @return $this
     */
    public function &defaultTtl($value);

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
