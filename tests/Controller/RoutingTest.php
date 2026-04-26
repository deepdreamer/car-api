<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class RoutingTest extends ApiTestCase
{
    public function testUnknownRouteReturnsNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/nonsense');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
