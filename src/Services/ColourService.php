<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\CreateColourRequest;
use App\Entity\Colour;
use App\Repository\ColourRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ColourService
{
    public function __construct(
        private ColourRepository $colourRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @return array{data: list<array{id: int|null, name: string}>,
     *     pagination: array{page: int, limit: int, total: int, pages: int}}
     */
    public function getAllColoursAsForApiOutput(int $page, int $limit): array
    {
        $total = $this->colourRepository->count();
        $colours = $this->colourRepository->findBy([], null, $limit, ($page - 1) * $limit);

        return [
            'data' => array_values(array_map(fn(Colour $colour) => [
                'id' => $colour->getId(),
                'name' => $colour->getName(),
            ], $colours)),
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
     * @return CreateColourRequest|array{errors: array<string, string>}
     */
    public function createValidatedColourRequest(array $body): CreateColourRequest|array
    {
        $name = $body['name'] ?? null;

        if (!is_string($name)) {
            return ['errors' => ['request' => 'Invalid or missing fields. Expected: name (string).']];
        }

        $dto = new CreateColourRequest(name: $name);

        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = (string) $violation->getMessage();
            }

            return ['errors' => $errors];
        }

        if ($this->colourRepository->findOneBy(['name' => $name]) !== null) {
            return ['errors' => ['name' => 'Colour already exists.']];
        }

        return $dto;
    }

    /**
     * @return array{id: int|null, name: string}
     */
    public function createColour(CreateColourRequest $request): array
    {
        $colour = new Colour();
        $colour->setName($request->name);

        $this->entityManager->persist($colour);
        $this->entityManager->flush();

        return [
            'id' => $colour->getId(),
            'name' => $colour->getName(),
        ];
    }
}
