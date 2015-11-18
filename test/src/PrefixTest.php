<?php

namespace ActiveCollab\Cookies\Test;

use ActiveCollab\Cookies\Test\Adapter\Test;
use ActiveCollab\Cookies\CookiesInterface;
use ActiveCollab\Cookies\Cookies;
use ActiveCollab\Cookies\Test\Base\TestCase;

/**
 * @package ActiveCollab\Cookies\Test
 */
class PrefixTest extends TestCase
{
    /**
     * @var CookiesInterface
     */
    private $cookies;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        parent::setUp();

        $this->cookies = (new Cookies(new Test([
            'other_websites_cookie' => 123,
            'prefix_test_our_cookie' => 987,
        ])))->prefix('prefix_test_');
    }

    /**
     * Test if exists checks for prefixed values
     */
    public function testExists()
    {
        $this->assertFalse($this->cookies->exists('other_websites_cookie'));
        $this->assertTrue($this->cookies->exists('our_cookie'));
    }

    /**
     * Test if get returns prefixed values
     */
    public function testGet()
    {
        $this->assertEmpty($this->cookies->get('other_websites_cookie'));
        $this->assertSame(987, $this->cookies->get('our_cookie'));
    }

    /**
     * Test if set adds prefixed cookied value
     */
    public function testSet()
    {
        $this->assertFalse($this->cookies->exists('new_cookie'));
        $this->cookies->set('new_cookie', 'new_cookie_value');
        $this->assertTrue($this->cookies->exists('new_cookie'));
        $this->assertSame('new_cookie_value', $this->cookies->get('new_cookie'));
    }

    /**
     * Test if remove unsets prefixed cookie value
     */
    public function testRemove()
    {
        $this->assertTrue($this->cookies->exists('our_cookie'));
        $this->cookies->remove('our_cookie');
        $this->assertFalse($this->cookies->exists('our_cookie'));
    }
}
