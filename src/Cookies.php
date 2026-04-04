<?php

/*
 * This file is part of the Active Collab Cookies project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Cookies;

use ActiveCollab\Cookies\Adapter\CookieGetter;
use ActiveCollab\Cookies\Adapter\CookieGetterInterface;
use ActiveCollab\Cookies\Adapter\CookieRemover;
use ActiveCollab\Cookies\Adapter\CookieRemoverInterface;
use ActiveCollab\Cookies\Adapter\CookieSetter;
use ActiveCollab\Cookies\Adapter\CookieSetterInterface;
use ActiveCollab\CurrentTimestamp\CurrentTimestamp;
use ActiveCollab\CurrentTimestamp\CurrentTimestampInterface;
use ActiveCollab\Encryptor\EncryptorInterface;
use Dflydev\FigCookies\Modifier\SameSite;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Cookies implements CookiesInterface
{
    private ?CurrentTimestampInterface $currentTimestamp;
    private ?EncryptorInterface $encryptor;
    private SameSite $sameSite;

    public function __construct(
        CurrentTimestampInterface $currentTimestamp = null,
        EncryptorInterface $encryptor = null
    )
    {
        $this->currentTimestamp = $currentTimestamp;
        $this->encryptor = $encryptor;

        if (empty($this->currentTimestamp)) {
            $this->currentTimestamp = new CurrentTimestamp();
        }

        $this->sameSite = SameSite::lax();
    }

    public function createGetter(string $name): CookieGetterInterface
    {
        return new CookieGetter($this->getPrefixedName($name));
    }

    public function exists(ServerRequestInterface $request, string $name): bool
    {
        return $this->createGetter($name)->exists($request);
    }

    public function get(
        ServerRequestInterface $request,
        string $name,
        $default = null,
        bool $decrypt = true,
    ): mixed
    {
        $cookieReader = $this->createGetter($name);

        if ($cookieReader->exists($request)) {
            $value = $cookieReader->get($request, $default);

            if ($decrypt && $this->encryptor) {
                $value = $this->encryptor->decrypt($value);
            }

            return $value;
        }

        return $default;
    }

    public function createSetter(
        string $name,
        $value,
        array $settings = [],
    ): CookieSetterInterface
    {
        $encrypt = array_key_exists('encrypt', $settings) ? $settings['encrypt'] : true;

        if ($encrypt && $this->encryptor) {
            $value = $this->encryptor->encrypt($value);
        }

        return new CookieSetter(
            $this->getPrefixedName($name),
            $value,
            $this->prepareSettings($settings),
            $this->currentTimestamp
        );
    }

    public function set(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $name,
        $value,
        array $settings = [],
    ): array
    {
        $cookieSetter = $this->createSetter(
            $name,
            $value,
            $settings,
        );

        return [
            $cookieSetter->applyToRequest($request),
            $cookieSetter->applyToResponse($response),
        ];
    }

private function prepareSettings(array $settings): array
{
    $settings['domain'] = $this->getDomain();
    $settings['path'] = $this->getPath();
    $settings['secure'] = $this->getSecure();

    if (empty($settings['ttl'])) {
        $settings['ttl'] = $this->getDefaultTtl();
    }

    if (!array_key_exists('http_only', $settings)) {
        $settings['http_only'] = $this->getHttpOnly();
    }

    if (!array_key_exists('same_site', $settings)) {
        $settings['same_site'] = $this->getSameSite();
    }

    $settings['expires'] = $this->currentTimestamp->getCurrentTimestamp() + $settings['ttl'];

    return $settings;
}

    public function createRemover(string $name): CookieRemoverInterface
    {
        return new CookieRemover(
            $this->getPrefixedName($name),
            $this->prepareSettings([]),
            $this->currentTimestamp,
        );
    }

    public function remove(ServerRequestInterface $request, ResponseInterface $response, string $name): array
    {
        $cookieRemover = $this->createRemover($name);

        return [
            $cookieRemover->applyToRequest($request),
            $cookieRemover->applyToResponse($response),
        ];
    }

    private function getPrefixedName($name)
    {
        return $this->getPrefix() . $name;
    }

    // ---------------------------------------------------
    //  Configuration
    // ---------------------------------------------------

    private int $defaultTtl = 1209600;

    public function getDefaultTtl(): int
    {
        return $this->defaultTtl;
    }

    public function defaultTtl(int $value): CookiesInterface
    {
        $this->defaultTtl = $value;

        return $this;
    }

    private string $domain = '';

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function domain(string $domain): CookiesInterface
    {
        $this->domain = $domain;

        return $this;
    }

    private string $path = '/';

    public function getPath(): string
    {
        return $this->path;
    }

    public function path(string $path): CookiesInterface
    {
        $this->path = $path;

        return $this;
    }

    private bool $secure = true;
    private bool $httpOnly = true;

    public function getSecure(): bool
    {
        return $this->secure;
    }

    public function secure(bool $secure): CookiesInterface
    {
        $this->secure = $secure;

        return $this;
    }

    public function getHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    public function httpOnly(bool $httpOnly): CookiesInterface
    {
        $this->httpOnly = $httpOnly;

        return $this;
    }

    public function getSameSite(): SameSite
    {
        return $this->sameSite;
    }

    public function sameSite(SameSite $sameSite): CookiesInterface
    {
        $this->sameSite = $sameSite;

        return $this;
    }

    private string $prefix = '';

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function prefix(string $prefix): CookiesInterface
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getEncryptor(): ?EncryptorInterface
    {
        return $this->encryptor;
    }

    public function encryptor(EncryptorInterface $encryptor = null): CookiesInterface
    {
        $this->encryptor = $encryptor;

        return $this;
    }

    public function configureFromUrl(string $url): CookiesInterface
    {
        $parts = parse_url($url);

        if (!empty($parts['scheme'])) {
            $this->secure(strtolower($parts['scheme']) === 'https');
        }

        $this->domain($parts['host']);

        if (empty($parts['path'])) {
            if ($this->getPath() != '/') {
                $this->path('/');
            }
        } else {
            $this->path('/' . trim($parts['path'], '/'));
        }

        if (empty($this->getPrefix())) {
            $this->prefix(md5($url));
        }

        return $this;
    }
}
