<?php

declare(strict_types=1);

namespace App\Controller;


use App\Repository\ColourRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/colours')]
class ColourController
{
    #[Route('', methods: ['GET'])]
    public function list(ColourRepository $colourRepository): JsonResponse
    {
        //@TODO: to be implemented

        return new JsonResponse([]);
    }

    #[Route('', methods: ['POST'])]
    public function create(ColourRepository $colourRepository): JsonResponse
    {
        //@TODO: to be implemented

        return new JsonResponse([]);
    }

    #[Route('/{id}', methods: ['PATCH'])]
    public function edit(ColourRepository $colourRepository): JsonResponse
    {
        //@TODO: to be implemented

        return new JsonResponse([]);
    }

}
