<?php

namespace ActiveCollab\Cookies\Test\Adapter;

use ActiveCollab\Cookies\Adapter\AdapterInterface;

/**
 * @package ActiveCollab\Cookies\Adapter
 */
class Test implements AdapterInterface
{
    /**
     * @var array
     */
    private $data;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $default = null)
    {
        return isset($this->data[$name]) ? $this->data[$name] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value, $ttl, $http_only)
    {
        $this->data[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($name)
    {
        if ($this->exists($name)) {
            unset($this->data[$name]);
        }
    }
}
