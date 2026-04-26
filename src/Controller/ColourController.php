<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Trait\PaginationTrait;
use App\Services\ColourService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/colours')]
class ColourController
{
    use PaginationTrait;

    public function __construct(private ColourService $colourService)
    {
    }

    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        ['page' => $page, 'limit' => $limit] = $this->getPaginationParams($request);

        return new JsonResponse($this->colourService->getAllColoursForApiOutput($page, $limit));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true);

        if (!is_array($body)) {
            return new JsonResponse(['error' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        /** @var array<string, mixed> $body */
        $validated = $this->colourService->createValidatedColourRequest($body);

        if (is_array($validated)) {
            return new JsonResponse($validated, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse($this->colourService->createColour($validated), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PATCH'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        if (!$this->colourService->colourExists($id)) {
            return new JsonResponse(['error' => 'Colour not found.'], Response::HTTP_NOT_FOUND);
        }

        $body = json_decode($request->getContent(), true);

        if (!is_array($body)) {
            return new JsonResponse(['error' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        /** @var array<string, mixed> $body */
        $validated = $this->colourService->createValidatedEditColourRequest($id, $body);

        if (is_array($validated)) {
            return new JsonResponse($validated, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse($this->colourService->editColour($id, $validated));
    }
}
