<?php

/*
 * This file is part of the Active Collab Cookies project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Cookies\Test;

use ActiveCollab\Cookies\Adapter\Adapter;
use ActiveCollab\Cookies\Cookies;
use ActiveCollab\Cookies\Encryptor\Encryptor;
use ActiveCollab\Cookies\Test\TestCase\TestCase;
use Dflydev\FigCookies\Cookies as FigCookies;

/**
 * @package ActiveCollab\Cookies\Test
 */
class EncryptionTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Key needs to be a non-empty string value
     */
    public function testExceptionOnEmptyKey()
    {
        new Encryptor('');
    }

    public function testAcceptableKeys()
    {
        new Encryptor('not 256 bit');
        new Encryptor('770A8A65DA156D24EE2A093277530142');
    }

    public function testEncryptAndDecryptValues()
    {
        $encryptor = new Encryptor('770A8A65DA156D24EE2A093277530142');

        $value_to_encrypt = 'Super secret value';

        $encrypted_value = $encryptor->encrypt($value_to_encrypt);

        $this->assertInternalType('string', $encrypted_value);
        $this->assertNotEmpty($encrypted_value);
        $this->assertNotEquals($value_to_encrypt, $encrypted_value);

        $this->assertEquals('Super secret value', $encryptor->decrypt($encrypted_value));
    }

    /**
     * Test value encryption.
     */
    public function testEncryption()
    {
        $cookies = (new Cookies(new Adapter()))->encryptor(new Encryptor('770A8A65DA156D24EE2A093277530142'));

        list($this->request, $this->response) = $cookies->set($this->request, $this->response, 'encrypted_var', 'value to encrypt');

        $raw_value = FigCookies::fromRequest($this->request)->get('encrypted_var')->getValue();

        $this->assertNotEmpty($raw_value);
        $this->assertNotEquals('value to encrypt', $raw_value);

        $this->assertEquals('value to encrypt', $cookies->get($this->request, 'encrypted_var'));
    }
}
