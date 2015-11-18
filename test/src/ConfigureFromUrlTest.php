<?php

namespace ActiveCollab\Cookies\Test;

use ActiveCollab\Cookies\Adapter\Test;
use ActiveCollab\Cookies\CookiesInterface;
use ActiveCollab\Cookies\Cookies;
use ActiveCollab\Cookies\Test\Base\TestCase;

/**
 * @package ActiveCollab\Cookies\Test
 */
class ConfigureFromUrlTest extends TestCase
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

        $this->cookies = new Cookies(new Test());
    }

    /**
     * Test domain
     */
    public function testDomain()
    {
        $this->assertEquals('activecollab.com', $this->cookies->configureFromUrl('https://activecollab.com/projects')->getDomain());
    }

    /**
     * Test path from URL
     */
    public function testPath()
    {
        $this->assertEquals('/', $this->cookies->configureFromUrl('https://activecollab.com')->getPath());
        $this->assertEquals('/', $this->cookies->configureFromUrl('https://activecollab.com/')->getPath());
        $this->assertEquals('/projects', $this->cookies->configureFromUrl('https://activecollab.com/projects')->getPath());
        $this->assertEquals('/projects/12/xyz', $this->cookies->configureFromUrl('https://activecollab.com/projects/12/xyz?index=true')->getPath());
    }

    /**
     * Test non-secure on HTTP
     */
    public function testNotSecureOnHttp()
    {
        $this->assertFalse($this->cookies->configureFromUrl('http://activecollab.com/projects')->getSecure());
    }

    /**
     * Test secure on HTTPS
     */
    public function testSecureOnHttps()
    {
        $this->assertTrue($this->cookies->configureFromUrl('https://activecollab.com/projects')->getSecure());
    }

    /**
     * Test prefix is URL hash when prefix is not set
     */
    public function testPrefixIsUrlHash()
    {
        $this->assertEquals(md5('https://activecollab.com/projects'), $this->cookies->configureFromUrl('https://activecollab.com/projects')->getPrefix());
    }

    /**
     * Test if prefix is not auto-set when it is already set
     */
    public function testPrefixIsNotSetIfAlreadySet()
    {
        $this->assertEquals('first_', $this->cookies->prefix('first_')->getPrefix());
        $this->assertEquals('first_', $this->cookies->configureFromUrl('https://activecollab.com/projects')->getPrefix());
    }
}
