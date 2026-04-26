<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Trait\PaginationTrait;
use App\Services\CarService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/cars')]
class CarController
{
    use PaginationTrait;

    public function __construct(private CarService $carService)
    {
    }

    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        ['page' => $page, 'limit' => $limit] = $this->getPaginationParams($request);

        return new JsonResponse($this->carService->getAllCarsAsForApiOutput($page, $limit));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true);

        if (!is_array($body)) {
            return new JsonResponse(['error' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        /** @var array<string, mixed> $body */
        $validated = $this->carService->createValidatedCarRequest($body);

        if (is_array($validated)) { // if its array it contains errors, if its valid then its object
            return new JsonResponse($validated, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse($this->carService->createCar($validated), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function remove(int $id): JsonResponse
    {
        if (!$this->carService->deleteCar($id)) {
            return new JsonResponse(['error' => 'Car not found.'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
