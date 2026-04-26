<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\CreateColourRequest;
use App\Entity\Colour;
use App\Repository\ColourRepository;
use App\Services\Trait\ViolationMapperTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ColourService
{
    use ViolationMapperTrait;

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
    public function getAllColoursForApiOutput(int $page, int $limit): array
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
        return $this->validateColourRequest($body);
    }

    public function colourExists(int $id): bool
    {
        return $this->colourRepository->find($id) !== null;
    }

    /**
     * @param array<string, mixed> $body
     * @return CreateColourRequest|array{errors: array<string, string>}
     */
    public function createValidatedEditColourRequest(int $id, array $body): CreateColourRequest|array
    {
        return $this->validateColourRequest($body, $id);
    }

    /**
     * @param array<string, mixed> $body
     * @return CreateColourRequest|array{errors: array<string, string>}
     */
    private function validateColourRequest(array $body, ?int $excludeId = null): CreateColourRequest|array
    {
        $name = $body['name'] ?? null;

        if (!is_string($name)) {
            return ['errors' => ['request' => 'Invalid or missing fields. Expected: name (string).']];
        }

        $dto = new CreateColourRequest(name: $name);

        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            return ['errors' => $this->mapViolations($violations)];
        }

        $existing = $this->colourRepository->findOneBy(['name' => $name]);
        if ($existing !== null && $existing->getId() !== $excludeId) {
            return ['errors' => ['name' => 'Colour already exists.']];
        }

        return $dto;
    }

    /**
     * @return array{id: int|null, name: string}
     */
    public function editColour(int $id, CreateColourRequest $request): array
    {
        $colour = $this->colourRepository->find($id);
        assert($colour !== null);

        $colour->setName($request->name);

        $this->entityManager->flush();

        return [
            'id' => $colour->getId(),
            'name' => $colour->getName(),
        ];
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
