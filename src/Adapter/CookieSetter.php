<?php

/*
 * This file is part of the Active Collab Cookies project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Cookies\Adapter;

use Dflydev\FigCookies\Cookie;
use Dflydev\FigCookies\Cookies;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\SetCookies;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CookieSetter implements CookieSetterInterface
{
    protected $name;
    private $value;
    private $domain;
    private $path;
    private $timeToLive;
    private $expires;
    private $secure;
    private $httpOnly;

    public function __construct(string $name, $value, array $settings = [])
    {
        $this->name = $name;
        $this->value = $value;

        $this->domain = isset($settings['domain']) ? (string) $settings['domain'] : '';
        $this->path = isset($settings['path']) ? (string) $settings['path'] : '/';
        $this->timeToLive = isset($settings['ttl']) ? $settings['ttl'] : 0;
        $this->expires = isset($settings['expires']) ? $settings['expires'] : time() + $this->timeToLive;
        $this->secure = isset($settings['secure']) && $settings['secure'];
        $this->httpOnly = isset($settings['http_only']) && $settings['http_only'];
    }

    public function applyToRequest(RequestInterface $request): RequestInterface
    {
        $cookies = Cookies::fromRequest($request);

        if ($cookies->has($this->name)) {
            $cookie = $cookies->get($this->name)->withValue($this->value);
        } else {
            $cookie = Cookie::create($this->name, $this->value);
        }

        return $cookies
            ->with($cookie)
            ->renderIntoCookieHeader($request);
    }

    public function applyToResponse(ResponseInterface $response): ResponseInterface
    {
        $setCookies = SetCookies::fromResponse($response);

        if ($setCookies->has($this->name)) {
            $set_cookie = $setCookies->get($this->name)->withValue($this->value);
        } else {
            $set_cookie = SetCookie::create($this->name, $this->value);
        }

        $set_cookie = $set_cookie
            ->withDomain($this->domain)
            ->withPath($this->path)
            ->withSecure($this->secure)
            ->withExpires(date(DATE_COOKIE, $this->expires))
            ->withHttpOnly($this->httpOnly);

        return $setCookies->with($set_cookie)->renderIntoSetCookieHeader($response);
    }
}
