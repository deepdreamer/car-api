<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Repository\CarRepository;
use App\Repository\ColourRepository;
use App\Tests\ApiTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class CarControllerTest extends ApiTestCase
{
    private int $validColourId;
    private int $validCarId;

    protected function setUp(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $loader = new Loader();
        $loader->addFixture(new AppFixtures());

        $executor = new ORMExecutor($em, new ORMPurger($em));
        $executor->execute($loader->getFixtures());

        /** @var ColourRepository $colourRepo */
        $colourRepo = self::getContainer()->get(ColourRepository::class);
        $colour = $colourRepo->findOneBy([]);
        $this->validColourId = (int) $colour?->getId();

        /** @var CarRepository $carRepo */
        $carRepo = self::getContainer()->get(CarRepository::class);
        $car = $carRepo->findOneBy([]);
        $this->validCarId = (int) $car?->getId();

        self::ensureKernelShutdown();
    }

    public function testListReturnsOk(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/cars');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }

    public function testListResponseShape(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/cars');

        $data = $this->decodeResponse($client->getResponse()->getContent());

        $this->assertPaginatedResponseShape($data);
    }

    public function testListDefaultPagination(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/cars');

        $data = $this->decodeResponse($client->getResponse()->getContent());
        $pagination = $data['pagination'];
        $this->assertIsArray($pagination);

        $this->assertSame(1, $pagination['page']);
        $this->assertSame(10, $pagination['limit']);
        $this->assertSame(10, $pagination['total']);
        $this->assertIsArray($data['data']);
        $this->assertCount(10, $data['data']);
    }

    public function testListCustomLimit(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/cars?limit=3');

        $data = $this->decodeResponse($client->getResponse()->getContent());
        $pagination = $data['pagination'];
        $this->assertIsArray($pagination);
        $this->assertIsArray($data['data']);

        $this->assertSame(3, $pagination['limit']);
        $this->assertCount(3, $data['data']);
    }

    public function testListSecondPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/cars?page=2&limit=6');

        $data = $this->decodeResponse($client->getResponse()->getContent());
        $pagination = $data['pagination'];
        $this->assertIsArray($pagination);
        $this->assertIsArray($data['data']);

        $this->assertSame(2, $pagination['page']);
        $this->assertCount(4, $data['data']); // 10 total, 6 on page 1, 4 on page 2
    }

    public function testListClampsNegativePageToOne(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/cars?page=-5');

        $data = $this->decodeResponse($client->getResponse()->getContent());
        $pagination = $data['pagination'];
        $this->assertIsArray($pagination);

        $this->assertSame(1, $pagination['page']);
    }

    public function testListCarShape(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/cars?limit=1');

        $data = $this->decodeResponse($client->getResponse()->getContent());
        $this->assertIsArray($data['data']);
        $car = $data['data'][0];
        $this->assertIsArray($car);

        $this->assertArrayHasKey('id', $car);
        $this->assertArrayHasKey('make', $car);
        $this->assertArrayHasKey('model', $car);
        $this->assertArrayHasKey('buildDate', $car);
        $this->assertArrayHasKey('colour', $car);
        $this->assertIsInt($car['id']);
        $this->assertIsString($car['buildDate']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $car['buildDate']);
    }

    public function testListReturnsEmptyDataWhenNoCars(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        new ORMPurger($em)->purge();

        self::ensureKernelShutdown();

        $client = static::createClient();
        $client->request('GET', '/api/cars');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $this->decodeResponse($client->getResponse()->getContent());
        $this->assertIsArray($data['data']);
        $this->assertCount(0, $data['data']);

        $this->assertIsArray($data['pagination']);
        $this->assertSame(0, $data['pagination']['total']);
        $this->assertSame(0, $data['pagination']['pages']);
    }

    public function testCreateReturnsCreated(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'make' => 'Toyota',
            'model' => 'Yaris',
            'buildDate' => '2023-01-01',
            'colourId' => $this->validColourId,
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }

    public function testCreateResponseShape(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'make' => 'Toyota',
            'model' => 'Yaris',
            'buildDate' => '2023-01-01',
            'colourId' => $this->validColourId,
        ]));

        $data = $this->decodeResponse($client->getResponse()->getContent());

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('make', $data);
        $this->assertArrayHasKey('model', $data);
        $this->assertArrayHasKey('buildDate', $data);
        $this->assertArrayHasKey('colour', $data);
        $this->assertIsInt($data['id']);
        $this->assertSame('Toyota', $data['make']);
        $this->assertSame('Yaris', $data['model']);
        $this->assertSame('2023-01-01', $data['buildDate']);
        $this->assertIsString($data['colour']);
    }

    public function testCreateReturnsBadRequestForInvalidJson(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'], 'not-valid-json');

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $data = $this->decodeResponse($client->getResponse()->getContent());
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Invalid JSON body.', $data['error']);
    }

    public function testCreateReturnsUnprocessableForMissingFields(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'make' => 'Toyota',
        ]));

        $this->assertUnprocessableWithError($client, 'request', 'Invalid or missing fields. Expected: make (string), model (string), buildDate (string Y-m-d), colourId (integer).');
    }

    public function testCreateReturnsUnprocessableWhenColourIdIsNotInteger(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'make' => 'Toyota',
            'model' => 'Yaris',
            'buildDate' => '2023-01-01',
            'colourId' => 'red',
        ]));

        $this->assertUnprocessableWithError($client, 'request', 'Invalid or missing fields. Expected: make (string), model (string), buildDate (string Y-m-d), colourId (integer).');
    }

    public function testCreateReturnsUnprocessableForInvalidDateFormat(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'make' => 'Toyota',
            'model' => 'Yaris',
            'buildDate' => '01-01-2023',
            'colourId' => $this->validColourId,
        ]));

        $this->assertUnprocessableWithError($client, 'buildDate', 'Invalid date format. Expected Y-m-d.');
    }

    public function testCreateReturnsUnprocessableForBlankMake(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'make' => '',
            'model' => 'Yaris',
            'buildDate' => '2023-01-01',
            'colourId' => $this->validColourId,
        ]));

        $this->assertUnprocessableWithError($client, 'make', 'This value should not be blank.');
    }

    public function testCreateReturnsUnprocessableForBlankModel(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'make' => 'Toyota',
            'model' => '',
            'buildDate' => '2023-01-01',
            'colourId' => $this->validColourId,
        ]));

        $this->assertUnprocessableWithError($client, 'model', 'This value should not be blank.');
    }

    public function testCreateReturnsUnprocessableForBuildDateOlderThanFourYears(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'make' => 'Toyota',
            'model' => 'Yaris',
            'buildDate' => '2020-01-01',
            'colourId' => $this->validColourId,
        ]));

        $this->assertUnprocessableWithError($client, 'buildDate', 'The build date cannot be older than 4 years.');
    }

    public function testCreateReturnsUnprocessableForNonExistentColourId(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/cars', [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'make' => 'Toyota',
            'model' => 'Yaris',
            'buildDate' => '2023-01-01',
            'colourId' => 99999,
        ]));

        $this->assertUnprocessableWithError($client, 'colourId', 'Colour not found.');
    }

    public function testDeleteReturnsNoContent(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/cars/' . $this->validCarId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteActuallyRemovesCar(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/cars/' . $this->validCarId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $client->request('DELETE', '/api/cars/' . $this->validCarId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteReturnsNotFoundForNonExistentId(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/cars/99999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $data = $this->decodeResponse($client->getResponse()->getContent());
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Car not found.', $data['error']);
    }
}
