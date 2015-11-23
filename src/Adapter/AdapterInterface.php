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
     * Set or modify a cookie value
     *
     * Available settings:
     *
     * - domain
     * - path
     * - ttl
     * - secure
     * - http_only
     *
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * @param  string                 $name
     * @param  string                 $value
     * @param  array                  $settings
     * @return array
     */
    public function set(ServerRequestInterface $request, ResponseInterface $response, $name, $value, array $settings = []);

    /**
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * @param  string                 $name
     * @return  array
     */
    public function remove(ServerRequestInterface $request, ResponseInterface $response, $name);
}
