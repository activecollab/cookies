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

class ConfigureFromUrlTest extends TestCase
{
    /**
     * @var CookiesInterface
     */
    private $cookies;

    public function setUp(): void
    {
        parent::setUp();

        $this->cookies = new Cookies();
    }

    /**
     * Test domain.
     */
    public function testDomain()
    {
        $this->assertEquals('activecollab.com', $this->cookies->configureFromUrl('https://activecollab.com/projects')->getDomain());
    }

    /**
     * Test path from URL.
     */
    public function testPath()
    {
        $this->assertEquals('/', $this->cookies->configureFromUrl('https://activecollab.com')->getPath());
        $this->assertEquals('/', $this->cookies->configureFromUrl('https://activecollab.com/')->getPath());
        $this->assertEquals('/projects', $this->cookies->configureFromUrl('https://activecollab.com/projects')->getPath());
        $this->assertEquals('/projects/12/xyz', $this->cookies->configureFromUrl('https://activecollab.com/projects/12/xyz?index=true')->getPath());
    }

    /**
     * Test non-secure on HTTP.
     */
    public function testNotSecureOnHttp()
    {
        $this->assertFalse($this->cookies->configureFromUrl('http://activecollab.com/projects')->getSecure());
    }

    /**
     * Test secure on HTTPS.
     */
    public function testSecureOnHttps()
    {
        $this->assertTrue($this->cookies->configureFromUrl('https://activecollab.com/projects')->getSecure());
    }

    /**
     * Test prefix is URL hash when prefix is not set.
     */
    public function testPrefixIsUrlHash()
    {
        $this->assertEquals(md5('https://activecollab.com/projects'), $this->cookies->configureFromUrl('https://activecollab.com/projects')->getPrefix());
    }

    /**
     * Test if prefix is not auto-set when it is already set.
     */
    public function testPrefixIsNotSetIfAlreadySet()
    {
        $this->assertEquals('first_', $this->cookies->prefix('first_')->getPrefix());
        $this->assertEquals('first_', $this->cookies->configureFromUrl('https://activecollab.com/projects')->getPrefix());
    }
}
