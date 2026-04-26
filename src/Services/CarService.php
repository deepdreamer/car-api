<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\CreateCarRequest;
use App\Entity\Car;
use App\Repository\CarRepository;
use App\Repository\ColourRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CarService
{
    public function __construct(
        private CarRepository $carRepository,
        private ColourRepository $colourRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @return array{data: list<array{id: int|null, make: string, model: string, buildDate: string, colour: string}>,
     *     pagination: array{page: int, limit: int, total: int, pages: int}}
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
                'pages' => $total === 0 ? 0 : (int) ceil($total / $limit),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $body
     * @return CreateCarRequest|array{errors: array<string, string>}
     */
    public function createValidatedCarRequest(array $body): CreateCarRequest|array
    {
        $make = $body['make'] ?? null;
        $model = $body['model'] ?? null;
        $buildDateRaw = $body['buildDate'] ?? null;
        $colourId = $body['colourId'] ?? null;

        if (!is_string($make) || !is_string($model) || !is_string($buildDateRaw) || !is_int($colourId)) {
            return ['errors' => ['request' => 'Invalid or missing fields. Expected: make (string), model (string), buildDate (string Y-m-d), colourId (integer).']];
        }

        $buildDate = \DateTimeImmutable::createFromFormat('Y-m-d', $buildDateRaw);

        if ($buildDate === false) {
            return ['errors' => ['buildDate' => 'Invalid date format. Expected Y-m-d.']];
        }

        $dto = new CreateCarRequest(
            make: $make,
            model: $model,
            buildDate: $buildDate,
            colourId: $colourId,
        );

        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = (string) $violation->getMessage();
            }

            return ['errors' => $errors];
        }

        if ($this->colourRepository->find($dto->colourId) === null) {
            return ['errors' => ['colourId' => 'Colour not found.']];
        }

        return $dto;
    }

    /**
     * @return array{id: int|null, make: string, model: string, buildDate: string, colour: string}
     */
    public function createCar(CreateCarRequest $request): array
    {
        $colour = $this->colourRepository->find($request->colourId);
        assert($colour !== null);

        $car = new Car();
        $car->setMake($request->make);
        $car->setModel($request->model);
        $car->setBuildDate(DateTime::createFromImmutable($request->buildDate));
        $car->setColour($colour);

        $this->entityManager->persist($car);
        $this->entityManager->flush();

        return [
            'id' => $car->getId(),
            'make' => $car->getMake(),
            'model' => $car->getModel(),
            'buildDate' => $car->getBuildDate()->format('Y-m-d'),
            'colour' => $car->getColour()->getName(),
        ];
    }
}
