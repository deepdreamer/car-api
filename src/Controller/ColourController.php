<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Trait\PaginationTrait;
use App\Services\ColourService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

        return new JsonResponse($this->colourService->getAllColoursAsForApiOutput($page, $limit));
    }

    #[Route('', methods: ['POST'])]
    public function create(): JsonResponse
    {
        //@TODO: to be implemented

        return new JsonResponse([]);
    }

    #[Route('/{id}', methods: ['PATCH'])]
    public function edit(): JsonResponse
    {
        //@TODO: to be implemented

        return new JsonResponse([]);
    }
}
