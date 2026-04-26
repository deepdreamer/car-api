<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CarControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $loader = new Loader();
        $loader->addFixture(new AppFixtures());

        $executor = new ORMExecutor($em, new ORMPurger($em));
        $executor->execute($loader->getFixtures());

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

        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('pagination', $data);

        $this->assertIsArray($data['pagination']);
        $this->assertArrayHasKey('page', $data['pagination']);
        $this->assertArrayHasKey('limit', $data['pagination']);
        $this->assertArrayHasKey('total', $data['pagination']);
        $this->assertArrayHasKey('pages', $data['pagination']);
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

    /** @return array<string, mixed> */
    private function decodeResponse(string|false $content): array
    {
        $this->assertIsString($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);

        /** @var array<string, mixed> $data */
        return $data;
    }
}
