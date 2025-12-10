<?php

namespace App\Entity;

use App\Repository\RouteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RouteRepository::class)]
class Route
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $startLat = null;

    #[ORM\Column]
    private ?float $startLng = null;

    #[ORM\Column]
    private ?float $endLat = null;

    #[ORM\Column]
    private ?float $endLng = null;

    #[ORM\Column(nullable: true)]
    private ?int $distance = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartLat(): ?float
    {
        return $this->startLat;
    }
    public function setStartLat(float $lat): static
    {
        $this->startLat = $lat;
        return $this;
    }

    public function getStartLng(): ?float
    {
        return $this->startLng;
    }
    public function setStartLng(float $lng): static
    {
        $this->startLng = $lng;
        return $this;
    }

    public function getEndLat(): ?float
    {
        return $this->endLat;
    }
    public function setEndLat(float $lat): static
    {
        $this->endLat = $lat;
        return $this;
    }

    public function getEndLng(): ?float
    {
        return $this->endLng;
    }
    public function setEndLng(float $lng): static
    {
        $this->endLng = $lng;
        return $this;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }
    public function setDistance(?int $distance): static
    {
        $this->distance = $distance;
        return $this;
    }
}
