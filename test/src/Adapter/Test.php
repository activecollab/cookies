<?php

namespace ActiveCollab\Cookies\Test\Adapter;

use ActiveCollab\Cookies\Adapter\AdapterInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

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
    public function exists(ServerRequestInterface $request, $name)
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function get(ServerRequestInterface $request, $name, $default = null)
    {
        return isset($this->data[$name]) ? $this->data[$name] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set(ResponseInterface $response, $name, $value, $ttl, $http_only)
    {
        $this->data[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ResponseInterface $response, $name)
    {
        if (array_key_exists($name, $this->data)) {
            unset($this->data[$name]);
        }
    }
}
