<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CarRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarRepository::class)]
class Car
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $make;

    #[ORM\Column(length: 255)]
    private string $model;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private \DateTime $buildDate;

    #[ORM\ManyToOne(targetEntity: Colour::class, inversedBy: 'cars')]
    #[ORM\JoinColumn(nullable: false)]
    private Colour $colour;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMake(): string
    {
        return $this->make;
    }

    public function setMake(string $make): static
    {
        $this->make = $make;

        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getBuildDate(): \DateTime
    {
        return $this->buildDate;
    }

    public function setBuildDate(\DateTime $buildDate): static
    {
        $this->buildDate = $buildDate;

        return $this;
    }

    public function getColor(): Colour
    {
        return $this->colour;
    }

    public function setColour(Colour $colour): static
    {
        $this->colour = $colour;

        return $this;
    }
}
