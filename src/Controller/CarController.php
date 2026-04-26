<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CarRepository;
use App\Services\CarService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/cars')]
class CarController
{
    public function __construct(private CarService $carService)
    {
    }

    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1)); // cannot be less than 1
        $limit = max(1, min(10000, $request->query->getInt('limit', 10))); // cannot be more than 10000

        return new JsonResponse($this->carService->getAllCarsAsForApiOutput($page, $limit));
    }

    #[Route('', methods: ['POST'])]
    public function create(CarRepository $carRepository): JsonResponse
    {
        //@TODO: to be implemented

        return new JsonResponse([]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function remove(CarRepository $carRepository): JsonResponse
    {
        //@TODO: to be implemented

        return new JsonResponse([]);
    }
}
