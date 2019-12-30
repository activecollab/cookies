<?php

/*
 * This file is part of the Active Collab Cookies project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Cookies;

use ActiveCollab\Cookies\Adapter\Adapter;
use ActiveCollab\Cookies\Adapter\AdapterInterface;
use ActiveCollab\CurrentTimestamp\CurrentTimestamp;
use ActiveCollab\CurrentTimestamp\CurrentTimestampInterface;
use ActiveCollab\Encryptor\EncryptorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Cookies implements CookiesInterface
{
    private $adapter;
    private $current_timestamp;
    private $encryptor;

    public function __construct(
        AdapterInterface $adapter = null,
        CurrentTimestampInterface $current_timestamp = null,
        EncryptorInterface $encryptor = null
    )
    {
        $this->adapter = $adapter ? $adapter : new Adapter();
        $this->current_timestamp = $current_timestamp;
        $this->encryptor = $encryptor;

        if (empty($this->current_timestamp)) {
            $this->current_timestamp = new CurrentTimestamp();
        }
    }

    public function exists(ServerRequestInterface $request, string $name): bool
    {
        return $this->adapter->exists($request, $this->getPrefixedName($name));
    }

    public function get(
        ServerRequestInterface $request,
        string $name,
        $default = null,
        array $settings = []
    )
    {
        if ($this->exists($request, $name)) {
            $value = $this->adapter->get($request, $this->getPrefixedName($name), $default);

            $decrypt = array_key_exists('decrypt', $settings) ? (bool) $settings['decrypt'] : true;

            if ($decrypt && $this->encryptor) {
                $value = $this->encryptor->decrypt($value);
            }

            return $value;
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
        $settings['domain'] = $this->getDomain();
        $settings['path'] = $this->getPath();
        $settings['secure'] = $this->getSecure();

        if (empty($settings['ttl'])) {
            $settings['ttl'] = $this->getDefaultTtl();
        }

        if (empty($settings['http_only'])) {
            $settings['http_only'] = false;
        }

        $encrypt = array_key_exists('encrypt', $settings) ? $settings['encrypt'] : true;

        if ($encrypt && $this->encryptor) {
            $value = $this->encryptor->encrypt($value);
        }

        $settings['expires'] = $this->current_timestamp->getCurrentTimestamp() + $settings['ttl'];

        return $this->adapter->set($request, $response, $this->getPrefixedName($name), $value, $settings);
    }

    public function remove(ServerRequestInterface $request, ResponseInterface $response, string $name)
    {
        return $this->adapter->remove($request, $response, $this->getPrefixedName($name));
    }
    
    private function getPrefixedName($name)
    {
        return $this->getPrefix() . $name;
    }

    // ---------------------------------------------------
    //  Configuration
    // ---------------------------------------------------

    private $default_ttl = 1209600;

    public function getDefaultTtl(): int
    {
        return $this->default_ttl;
    }

    public function defaultTtl(int $value): CookiesInterface
    {
        $this->default_ttl = $value;

        return $this;
    }

    /**
     * @var string
     */
    private $domain = '';

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function domain(string $domain): CookiesInterface
    {
        $this->domain = $domain;

        return $this;
    }

    private $path = '/';

    public function getPath(): string
    {
        return $this->path;
    }

    public function path(string $path): CookiesInterface
    {
        $this->path = $path;

        return $this;
    }

    private $secure = true;

    public function getSecure(): bool
    {
        return $this->secure;
    }

    public function secure(bool $secure): CookiesInterface
    {
        $this->secure = (bool) $secure;

        return $this;
    }

    private $prefix = '';

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
