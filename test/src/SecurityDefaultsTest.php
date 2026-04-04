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
use Dflydev\FigCookies\Modifier\SameSite;

class SecurityDefaultsTest extends TestCase
{
    private CookiesInterface $cookies;

    public function setUp(): void
    {
        parent::setUp();

        $this->cookies = new Cookies();
    }

    // --- Secure flag ---

    public function testSecureIsTrueByDefault(): void
    {
        $this->assertTrue($this->cookies->getSecure());
    }

    public function testSecureCanBeDisabled(): void
    {
        $this->cookies->secure(false);
        $this->assertFalse($this->cookies->getSecure());
    }

    public function testSecureAppliedToResponseHeader(): void
    {
        [, $response] = $this->cookies->set(
            $this->request,
            $this->response,
            'token',
            'abc',
        );

        $header = $response->getHeaderLine('Set-Cookie');
        $this->assertStringContainsString('Secure', $header);
    }

    public function testSecureOmittedWhenDisabled(): void
    {
        $this->cookies->secure(false);

        [, $response] = $this->cookies->set(
            $this->request,
            $this->response,
            'token',
            'abc',
        );

        $header = $response->getHeaderLine('Set-Cookie');
        $this->assertStringNotContainsString('Secure', $header);
    }

    // --- HttpOnly flag ---

    public function testHttpOnlyIsTrueByDefault(): void
    {
        $this->assertTrue($this->cookies->getHttpOnly());
    }

    public function testHttpOnlyCanBeDisabled(): void
    {
        $this->cookies->httpOnly(false);
        $this->assertFalse($this->cookies->getHttpOnly());
    }

    public function testHttpOnlyAppliedToResponseHeader(): void
    {
        [, $response] = $this->cookies->set(
            $this->request,
            $this->response,
            'session',
            'xyz',
        );

        $header = $response->getHeaderLine('Set-Cookie');
        $this->assertStringContainsString('HttpOnly', $header);
    }

    public function testHttpOnlyOmittedWhenDisabled(): void
    {
        $this->cookies->httpOnly(false);

        [, $response] = $this->cookies->set(
            $this->request,
            $this->response,
            'tracking',
            'xyz',
        );

        $header = $response->getHeaderLine('Set-Cookie');
        $this->assertStringNotContainsString('HttpOnly', $header);
    }

    // --- SameSite attribute ---

    public function testSameSiteIsLaxByDefault(): void
    {
        $this->assertSame(
            'SameSite=Lax',
            $this->cookies->getSameSite()->asString(),
        );
    }

    public function testSameSiteCanBeSetToStrict(): void
    {
        $this->cookies->sameSite(SameSite::strict());

        $this->assertSame(
            'SameSite=Strict',
            $this->cookies->getSameSite()->asString(),
        );
    }

    public function testSameSiteCanBeSetToNone(): void
    {
        $this->cookies->sameSite(SameSite::none());

        $this->assertSame(
            'SameSite=None',
            $this->cookies->getSameSite()->asString(),
        );
    }

    public function testSameSiteLaxAppliedToResponseHeader(): void
    {
        [, $response] = $this->cookies->set(
            $this->request,
            $this->response,
            'session',
            'val',
        );

        $header = $response->getHeaderLine('Set-Cookie');
        $this->assertStringContainsString('SameSite=Lax', $header);
    }

    public function testSameSiteStrictAppliedToResponseHeader(): void
    {
        $this->cookies->sameSite(SameSite::strict());

        [, $response] = $this->cookies->set(
            $this->request,
            $this->response,
            'csrf',
            'token123',
        );

        $header = $response->getHeaderLine('Set-Cookie');
        $this->assertStringContainsString('SameSite=Strict', $header);
    }

    public function testSameSiteNoneRequiresSecure(): void
    {
        $this->cookies->sameSite(SameSite::none());
        $this->cookies->secure(true);

        [, $response] = $this->cookies->set(
            $this->request,
            $this->response,
            'cross_site',
            'data',
        );

        $header = $response->getHeaderLine('Set-Cookie');
        $this->assertStringContainsString('SameSite=None', $header);
        $this->assertStringContainsString('Secure', $header);
    }

    // --- Combined: configureFromUrl + security defaults ---

    public function testConfigureFromHttpsUrlSetsSecureAndPreservesDefaults(): void
    {
        $this->cookies->configureFromUrl('https://app.example.com/dashboard');

        $this->assertTrue($this->cookies->getSecure());
        $this->assertTrue($this->cookies->getHttpOnly());
        $this->assertSame('SameSite=Lax', $this->cookies->getSameSite()->asString());
    }

    public function testConfigureFromHttpUrlDisablesSecure(): void
    {
        $this->cookies->configureFromUrl('http://localhost:8080/dev');

        $this->assertFalse($this->cookies->getSecure());
        // HttpOnly and SameSite remain at secure defaults
        $this->assertTrue($this->cookies->getHttpOnly());
        $this->assertSame('SameSite=Lax', $this->cookies->getSameSite()->asString());
    }

    // --- Full header integration ---

    public function testFullSecurityHeadersAppliedByDefault(): void
    {
        [, $response] = $this->cookies->set(
            $this->request,
            $this->response,
            'full_test',
            'value',
        );

        $header = $response->getHeaderLine('Set-Cookie');
        $this->assertStringContainsString('Secure', $header);
        $this->assertStringContainsString('HttpOnly', $header);
        $this->assertStringContainsString('SameSite=Lax', $header);
    }

    public function testSecuritySettingsCanBeOverriddenPerCookie(): void
    {
        [, $response] = $this->cookies->set(
            $this->request,
            $this->response,
            'js_accessible',
            'value',
            ['http_only' => false],
        );

        $header = $response->getHeaderLine('Set-Cookie');
        $this->assertStringNotContainsString('HttpOnly', $header);
        // Other defaults remain
        $this->assertStringContainsString('Secure', $header);
        $this->assertStringContainsString('SameSite=Lax', $header);
    }

    // --- Per-cookie same_site override ---

    public function testSameSiteCanBeOverriddenPerCookie(): void
    {
        $this->cookies->sameSite(SameSite::lax());

        [, $response] = $this->cookies->set(
            $this->request,
            $this->response,
            'csrf_token',
            'abc',
            ['same_site' => SameSite::strict()],
        );

        $header = $response->getHeaderLine('Set-Cookie');
        $this->assertStringContainsString('SameSite=Strict', $header);
        $this->assertStringNotContainsString('SameSite=Lax', $header);
    }

    public function testSameSiteOverrideDoesNotAffectGlobalDefault(): void
    {
        $this->cookies->sameSite(SameSite::lax());

        $this->cookies->set(
            $this->request,
            $this->response,
            'csrf_token',
            'abc',
            ['same_site' => SameSite::strict()],
        );

        $this->assertSame(
            'SameSite=Lax',
            $this->cookies->getSameSite()->asString(),
        );
    }

    public function testSameSiteUsesGlobalDefaultWhenNotOverridden(): void
    {
        $this->cookies->sameSite(SameSite::strict());

        [, $response] = $this->cookies->set(
            $this->request,
            $this->response,
            'session',
            'xyz',
        );

        $header = $response->getHeaderLine('Set-Cookie');
        $this->assertStringContainsString('SameSite=Strict', $header);
    }
}
