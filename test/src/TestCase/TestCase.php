<?php

/*
 * This file is part of the Active Collab Cookies project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Cookies\Test\TestCase;

use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Message\ServerRequestInterface;

abstract class TestCase extends BaseTestCase
{
    protected ServerRequestInterface $request;
    protected ResponseInterface $response;

    public function setUp(): void
    {
        parent::setUp();

        $this->request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://example.com:443/foo/bar?abc=123'
        );
        $this->response = (new ResponseFactory())->createResponse();
    }
}
