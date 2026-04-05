<?php

/*
 * This file is part of the Active Collab Cookies project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Cookies\Test;

use ActiveCollab\Cookies\Cookies;
use ActiveCollab\Cookies\CookiesInterface;
use ActiveCollab\Cookies\Test\TestCase\TestCase;

class CookieRemovalTest extends TestCase
{
    private CookiesInterface $cookies;

    public function setUp(): void
    {
        parent::setUp();

        $this->cookies = new Cookies();
    }

    public function testRemovalSetsExpiresInThePast(): void
    {
        [, $response] = $this->cookies->remove(
            $this->request,
            $this->response,
            'session',
        );

        $header = $response->getHeaderLine('Set-Cookie');

        $this->assertNotEmpty($header, 'Set-Cookie header should be present');

        preg_match('/Expires=([^;]+)/', $header, $matches);
        $this->assertNotEmpty($matches[1], 'Expires attribute should be present');

        $expiresTimestamp = strtotime($matches[1]);
        $this->assertLessThan(
            time(),
            $expiresTimestamp,
            'Removed cookie must expire in the past so the browser discards it',
        );
    }
}
