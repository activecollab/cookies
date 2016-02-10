<?php

namespace ActiveCollab\Cookies\Adapter;

use Dflydev\FigCookies\Cookie;
use Dflydev\FigCookies\Cookies;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\SetCookies;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @package ActiveCollab\Cookies\Adapter
 */
class Adapter implements AdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function exists(ServerRequestInterface $request, $name)
    {
        return Cookies::fromRequest($request)->has($name);
    }

    /**
     * {@inheritdoc}
     */
    public function get(ServerRequestInterface $request, $name, $default = null)
    {
        $cookies = Cookies::fromRequest($request);

        if ($cookies->has($name)) {
            return $cookies->get($name)->getValue();
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set(ServerRequestInterface $request, ResponseInterface $response, $name, $value, array $settings = [])
    {
        // ---------------------------------------------------
        //  Request cookies
        // ---------------------------------------------------

        $cookies = Cookies::fromRequest($request);

        if ($cookies->has($name)) {
            $cookie = $cookies->get($name)->withValue($value);
        } else {
            $cookie = Cookie::create($name, $value);
        }

        $request = $cookies->with($cookie)->renderIntoCookieHeader($request);

        // ---------------------------------------------------
        //  Response cookies
        // ---------------------------------------------------

        $domain = isset($settings['domain']) ? (string) $settings['domain'] : '';
        $path = isset($settings['path']) ? (string) $settings['path'] : '/';
        $ttl = isset($settings['ttl']) ? $settings['ttl'] : time();
        $secure = isset($settings['secure']) && $settings['secure'];
        $http_only = isset($settings['http_only']) && $settings['http_only'];

        $set_cookies = SetCookies::fromResponse($response);

        if ($set_cookies->has($name)) {
            $set_cookie = $set_cookies->get($name)->withValue($value);
        } else {
            $set_cookie = SetCookie::create($name, $value);
        }

        $set_cookie = $set_cookie->withDomain($domain)
            ->withPath($path)
            ->withSecure($secure)
            ->withExpires(date(DATE_COOKIE, $ttl))
            ->withHttpOnly($http_only);

        $response = $set_cookies->with($set_cookie)->renderIntoSetCookieHeader($response);

        return [$request, $response];
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ServerRequestInterface $request, ResponseInterface $response, $name)
    {
        list ($request, $response) = $this->set($request, $response, $name, '', ['ttl' => -172800]);

        $request = Cookies::fromRequest($request)->without($name)->renderIntoCookieHeader($request);

        return [$request, $response];
    }
}
