<?php

namespace App\Entity;

use App\Repository\MaintenanceRecordRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaintenanceRecordRepository::class)]
class MaintenanceRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $date = null;

    #[ORM\Column(length: 255)]
    private ?string $workType = null;

    #[ORM\Column]
    private ?float $cost = null;

    #[ORM\ManyToOne(inversedBy: 'maintenanceRecords')]
    private ?Vehicle $vehicle = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getWorkType(): ?string
    {
        return $this->workType;
    }

    public function setWorkType(string $workType): static
    {
        $this->workType = $workType;

        return $this;
    }

    public function getCost(): ?float
    {
        return $this->cost;
    }

    public function setCost(float $cost): static
    {
        $this->cost = $cost;

        return $this;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): static
    {
        $this->vehicle = $vehicle;

        return $this;
    }
}
