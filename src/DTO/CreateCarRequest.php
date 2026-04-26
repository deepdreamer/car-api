<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateCarRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $make,
        #[Assert\NotBlank]
        public string $model,
        #[Assert\GreaterThanOrEqual(value: '-4 years', message: 'The build date cannot be older than 4 years.')]
        public \DateTimeImmutable $buildDate,
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public int $colourId,
    ) {
    }
}
