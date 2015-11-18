<?php

namespace ActiveCollab\Cookies\Adapter;

/**
 * @package ActiveCollab\Cookies\Adapter
 */
class Test implements AdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value, $ttl, $http_only)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function remove($name)
    {
    }
}
