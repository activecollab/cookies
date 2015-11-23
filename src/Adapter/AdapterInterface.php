<?php

namespace ActiveCollab\Cookies\Adapter;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

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
     * @param  ServerRequestInterface $request
     * @param  string                 $name
     * @return boolean
     */
    public function exists(ServerRequestInterface $request, $name);

    /**
     * @param  ServerRequestInterface $request
     * @param  string                 $name
     * @param  mixed                  $default
     * @return mixed
     */
    public function get(ServerRequestInterface $request, $name, $default = null);

    /**
     * @param  ResponseInterface $response
     * @param  string            $name
     * @param  string            $value
     * @param  integer           $ttl
     * @param  bool              $http_only
     * @return mixed
     */
    public function set(ResponseInterface $response, $name, $value, $ttl, $http_only);

    /**
     * @param ResponseInterface $response
     * @param string            $name
     */
    public function remove(ResponseInterface $response, $name);
}
