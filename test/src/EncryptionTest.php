<?php

/*
 * This file is part of the Active Collab Authentication project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Cookies\Test;

use ActiveCollab\Cookies\Adapter\Adapter;
use ActiveCollab\Cookies\Cookies;
use ActiveCollab\Cookies\Encryptor\Encryptor;
use ActiveCollab\Cookies\Test\Base\TestCase;
use Dflydev\FigCookies\Cookies as FigCookies;

/**
 * @package ActiveCollab\Cookies\Test
 */
class EncryptionTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionOnEmptySecret()
    {
        new Encryptor('', MCRYPT_RIJNDAEL_128);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionOnInvalidAlgorithm()
    {
        new Encryptor('valid secret', 'not a valid algo');
    }

    /**
     * Test value encryption.
     */
    public function testEncryption()
    {
        $cookies = (new Cookies(new Adapter()))->encryptor(new Encryptor('this is a secret', MCRYPT_RIJNDAEL_256));

        list($this->request, $this->response) = $cookies->set($this->request, $this->response, 'encrypted_var', 'value to encrypt');

        $raw_value = FigCookies::fromRequest($this->request)->get('encrypted_var')->getValue();

        $this->assertNotEmpty($raw_value);
        $this->assertNotEquals('value to encrypt', $raw_value);

        $this->assertEquals('value to encrypt', $cookies->get($this->request, 'encrypted_var'));
    }
}
