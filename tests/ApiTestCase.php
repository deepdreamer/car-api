<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiTestCase extends WebTestCase
{
    /** @param array<string, mixed> $data */
    protected function assertPaginatedResponseShape(array $data): void
    {
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('pagination', $data);

        $this->assertIsArray($data['pagination']);
        $this->assertArrayHasKey('page', $data['pagination']);
        $this->assertArrayHasKey('limit', $data['pagination']);
        $this->assertArrayHasKey('total', $data['pagination']);
        $this->assertArrayHasKey('pages', $data['pagination']);
    }

    protected function assertUnprocessableWithError(KernelBrowser $client, string $field, string $message): void
    {
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $data = $this->decodeResponse($client->getResponse()->getContent());
        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertArrayHasKey($field, $data['errors']);
        $this->assertEquals($message, $data['errors'][$field]);
    }

    /** @return array<string, mixed> */
    protected function decodeResponse(string|false $content): array
    {
        $this->assertIsString($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);

        /** @var array<string, mixed> $data */
        return $data;
    }
}
