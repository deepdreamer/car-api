<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Colour;
use App\Repository\ColourRepository;

class ColourService
{
    public function __construct(private ColourRepository $colourRepository)
    {
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
}
