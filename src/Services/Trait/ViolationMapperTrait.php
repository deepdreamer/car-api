<?php

declare(strict_types=1);

namespace App\Services\Trait;

use Symfony\Component\Validator\ConstraintViolationListInterface;

trait ViolationMapperTrait
{
    /** @return array<string, string> */
    private function mapViolations(ConstraintViolationListInterface $violations): array
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = (string) $violation->getMessage();
        }

        return $errors;
    }
}
