<?php

/*
 * This file is part of the Active Collab Cookies project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Cookies\Adapter;

use Dflydev\FigCookies\Cookies;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Adapter implements AdapterInterface
{
    public function exists(ServerRequestInterface $request, string $name): bool
    {
        return Cookies::fromRequest($request)->has($name);
    }

    public function get(
        ServerRequestInterface $request,
        string $name,
        $default = null,
        array $settings = []
    )
    {
        $cookies = Cookies::fromRequest($request);

        if ($cookies->has($name)) {
            return $cookies->get($name)->getValue();
        }

        return $default;
    }

    public function set(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $name,
        $value,
        array $settings = []
    )
    {
        $cookieSetter = new CookieSetter($name, $value, $settings);

        return [
            $cookieSetter->applyToRequest($request),
            $cookieSetter->applyToResponse($response),
        ];
    }

    public function remove(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $name
    )
    {
        $cookieRemover = new CookieRemover($name);

        [
            $request,
            $response,
        ] = $this->set($request, $response, $name, '', ['ttl' => -172800]);

        $request = Cookies::fromRequest($request)->without($name)->renderIntoCookieHeader($request);

        return [
            $cookieRemover->applyToRequest($request),
            $cookieRemover->applyToResponse($response)
        ];
    }
}
