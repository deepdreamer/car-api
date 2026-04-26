<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Car;
use App\Repository\CarRepository;

class CarService
{
    public function __construct(private CarRepository $carRepository)
    {
    }

    /**
     * @return array{data: list<array{id: int|null, make: string, model: string, buildDate: string, colour: string}>, pagination: array{page: int, limit: int, total: int, pages: int}}
     */
    public function getAllCarsAsForApiOutput(int $page, int $limit): array
    {
        $total = $this->carRepository->count();
        $cars = $this->carRepository->findBy([], null, $limit, ($page - 1) * $limit);

        return [
            'data' => array_values(array_map(fn(Car $car) => [
                'id' => $car->getId(),
                'make' => $car->getMake(),
                'model' => $car->getModel(),
                'buildDate' => $car->getBuildDate()->format('Y-m-d'),
                'colour' => $car->getColour()->getName(),
            ], $cars)),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => (int) ceil($total / $limit),
            ],
        ];
    }
}
