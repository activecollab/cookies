<?php

namespace ActiveCollab\Cookies\Adapter;

/**
 * Interface AdapterInterface
 *
 * @package ActiveCollab\Cookies
 */
interface AdapterInterface
{
    /**
     * Return true if cookie with the given name exists
     *
     * @param  string  $name
     * @return boolean
     */
    public function exists($name);

    /**
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function get($name, $default = null);

    /**
     * @param  string  $name
     * @param  string  $value
     * @param  integer $ttl
     * @param  bool    $http_only
     * @return mixed
     */
    public function set($name, $value, $ttl, $http_only);

    /**
     * @param string $name
     */
    public function remove($name);
}
