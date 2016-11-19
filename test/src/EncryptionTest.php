<?php

/*
 * This file is part of the Active Collab Cookies project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Cookies\Test;

use ActiveCollab\Cookies\Adapter\Adapter;
use ActiveCollab\Cookies\Cookies;
use ActiveCollab\Encryptor\Encryptor;
use ActiveCollab\Cookies\Test\TestCase\TestCase;
use Dflydev\FigCookies\Cookies as FigCookies;

/**
 * @package ActiveCollab\Cookies\Test
 */
class EncryptionTest extends TestCase
{
    /**
     * Test cookie value encryption.
     */
    public function testCookieValueEncryption()
    {
        $cookies = (new Cookies(new Adapter()))->encryptor(new Encryptor('770A8A65DA156D24EE2A093277530142'));

        list($this->request, $this->response) = $cookies->set($this->request, $this->response, 'encrypted_var', 'value to encrypt');

        $raw_value = FigCookies::fromRequest($this->request)->get('encrypted_var')->getValue();

        $this->assertNotEmpty($raw_value);
        $this->assertNotEquals('value to encrypt', $raw_value);

        $this->assertEquals('value to encrypt', $cookies->get($this->request, 'encrypted_var'));
    }
}
