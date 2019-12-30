<?php

/*
 * This file is part of the Active Collab Cookies project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Cookies;

use ActiveCollab\Cookies\Adapter\AdapterInterface;

interface CookiesInterface extends AdapterInterface
{
    public function getDefaultTtl(): int;
    public function defaultTtl(int $value): CookiesInterface;

    public function getDomain(): string;
    public function domain(string $domain): CookiesInterface;

    public function getPath(): string;
    public function path(string $path): CookiesInterface;

    public function getSecure(): bool;
    public function secure(bool $secure): CookiesInterface;

    public function getPrefix(): string;
    public function prefix(string $prefix): CookiesInterface;

    public function configureFromUrl(string $url): CookiesInterface;
}
