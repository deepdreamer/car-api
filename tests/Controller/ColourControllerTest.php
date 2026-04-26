<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\ColourRepository;
use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ColourControllerTest extends ApiTestCase
{
    private int $validColourId;
    private string $validColourName;

    protected function setUp(): void
    {
        self::loadFixtures();

        /** @var ColourRepository $colourRepo */
        $colourRepo = self::getContainer()->get(ColourRepository::class);
        $colour = $colourRepo->findOneBy([]);
        $this->validColourId = (int) $colour?->getId();
        $this->validColourName = (string) $colour?->getName();

        self::ensureKernelShutdown();
    }

    public function testListReturnsOk(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/colours');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }

    public function testListResponseShape(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/colours');

        $data = $this->decodeResponse($client->getResponse()->getContent());

        $this->assertPaginatedResponseShape($data);
    }

    public function testListColourShape(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/colours?limit=1');

        $data = $this->decodeResponse($client->getResponse()->getContent());
        $this->assertIsArray($data['data']);
        $colour = $data['data'][0];
        $this->assertIsArray($colour);

        $this->assertArrayHasKey('id', $colour);
        $this->assertArrayHasKey('name', $colour);
        $this->assertIsInt($colour['id']);
        $this->assertIsString($colour['name']);
    }

    public function testListDefaultPagination(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/colours');

        $data = $this->decodeResponse($client->getResponse()->getContent());
        $pagination = $data['pagination'];
        $this->assertIsArray($pagination);

        $this->assertSame(1, $pagination['page']);
        $this->assertSame(10, $pagination['limit']);
        $this->assertSame(4, $pagination['total']); // 4 colours in fixtures
        $this->assertIsArray($data['data']);
        $this->assertCount(4, $data['data']);
    }

    public function testListCustomLimit(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/colours?limit=2');

        $data = $this->decodeResponse($client->getResponse()->getContent());
        $this->assertIsArray($data['data']);
        $this->assertIsArray($data['pagination']);

        $this->assertSame(2, $data['pagination']['limit']);
        $this->assertCount(2, $data['data']);
    }

    public function testListSecondPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/colours?page=2&limit=3');

        $data = $this->decodeResponse($client->getResponse()->getContent());
        $this->assertIsArray($data['data']);
        $this->assertIsArray($data['pagination']);

        $this->assertSame(2, $data['pagination']['page']);
        $this->assertCount(1, $data['data']); // 4 total, 3 on page 1, 1 on page 2
    }

    public function testCreateReturnsCreated(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/colours', [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'name' => 'Green',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }

    public function testCreateResponseShape(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/colours', [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'name' => 'Green',
        ]));

        $data = $this->decodeResponse($client->getResponse()->getContent());

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertIsInt($data['id']);
        $this->assertSame('Green', $data['name']);
    }

    public function testCreateReturnsBadRequestForInvalidJson(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/colours', [], [], ['CONTENT_TYPE' => 'application/json'], 'not-valid-json');

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $data = $this->decodeResponse($client->getResponse()->getContent());
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Invalid JSON body.', $data['error']);
    }

    public function testCreateReturnsUnprocessableForMissingName(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/colours', [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'foo' => 'bar',
        ]));

        $this->assertUnprocessableWithError($client, 'request', 'Invalid or missing fields. Expected: name (string).');
    }

    public function testCreateReturnsUnprocessableForBlankName(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/colours', [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'name' => '',
        ]));

        $this->assertUnprocessableWithError($client, 'name', 'This value should not be blank.');
    }

    public function testCreateReturnsUnprocessableForDuplicateName(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/colours', [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'name' => 'Red', // exists in fixtures
        ]));

        $this->assertUnprocessableWithError($client, 'name', 'Colour already exists.');
    }

    public function testEditReturnsOk(): void
    {
        $client = static::createClient();
        $client->request('PATCH', '/api/colours/' . $this->validColourId, [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'name' => 'Purple',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }

    public function testEditResponseShape(): void
    {
        $client = static::createClient();
        $client->request('PATCH', '/api/colours/' . $this->validColourId, [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'name' => 'Purple',
        ]));

        $data = $this->decodeResponse($client->getResponse()->getContent());

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertIsInt($data['id']);
        $this->assertSame('Purple', $data['name']);
    }

    public function testEditIsIdempotent(): void
    {
        $client = static::createClient();

        $client->request('PATCH', '/api/colours/' . $this->validColourId, [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'name' => $this->validColourName,
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->request('PATCH', '/api/colours/' . $this->validColourId, [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'name' => $this->validColourName,
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testEditReturnsBadRequestForInvalidJson(): void
    {
        $client = static::createClient();
        $client->request('PATCH', '/api/colours/' . $this->validColourId, [], [], ['CONTENT_TYPE' => 'application/json'], 'not-valid-json');

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $data = $this->decodeResponse($client->getResponse()->getContent());
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Invalid JSON body.', $data['error']);
    }


    public function testEditReturnsNotFoundForNonExistentId(): void
    {
        $client = static::createClient();
        $client->request('PATCH', '/api/colours/99999', [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'name' => 'Purple',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $data = $this->decodeResponse($client->getResponse()->getContent());
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Colour not found.', $data['error']);
    }

    public function testEditReturnsUnprocessableForMissingName(): void
    {
        $client = static::createClient();
        $client->request('PATCH', '/api/colours/' . $this->validColourId, [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'foo' => 'bar',
        ]));

        $this->assertUnprocessableWithError($client, 'request', 'Invalid or missing fields. Expected: name (string).');
    }

    public function testEditReturnsUnprocessableForBlankName(): void
    {
        $client = static::createClient();
        $client->request('PATCH', '/api/colours/' . $this->validColourId, [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'name' => '',
        ]));

        $this->assertUnprocessableWithError($client, 'name', 'This value should not be blank.');
    }

    public function testEditReturnsUnprocessableForDuplicateName(): void
    {
        $client = static::createClient();
        $client->request('PATCH', '/api/colours/' . $this->validColourId, [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'name' => 'Blue', // exists in fixtures as a different colour
        ]));

        $this->assertUnprocessableWithError($client, 'name', 'Colour already exists.');
    }

    public function testListReturnsEmptyDataWhenNoColours(): void
    {
        self::purgeDatabase();

        $client = static::createClient();
        $client->request('GET', '/api/colours');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $this->decodeResponse($client->getResponse()->getContent());
        $this->assertIsArray($data['data']);
        $this->assertCount(0, $data['data']);

        $this->assertIsArray($data['pagination']);
        $this->assertSame(0, $data['pagination']['total']);
        $this->assertSame(0, $data['pagination']['pages']);
    }
}
