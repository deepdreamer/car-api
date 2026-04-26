<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CarRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/cars')]
class CarController
{

    #[Route('', methods: ['GET'])]
    public function list(CarRepository $carRepository): JsonResponse
    {
        //@TODO: to be implemented

        return new JsonResponse([]);
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
