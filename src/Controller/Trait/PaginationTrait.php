<?php

declare(strict_types=1);

namespace App\Controller\Trait;

use Symfony\Component\HttpFoundation\Request;

trait PaginationTrait
{
    /** @return array{page: int, limit: int} */
    private function getPaginationParams(Request $request): array
    {
        return [
            'page' => max(1, $request->query->getInt('page', 1)),
            'limit' => max(1, min(10000, $request->query->getInt('limit', 10))),
        ];
    }
}
