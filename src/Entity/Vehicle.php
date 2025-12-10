<?php

namespace App\Entity;

use App\Repository\VehicleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 17)]
    private ?string $VIN = null;

    #[ORM\Column(length: 20)]
    private ?string $plateNumber = null;

    #[ORM\Column(length: 100)]
    private ?string $model = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column]
    private ?int $mileage = null;

    /**
     * @var Collection<int, Assignment>
     */
    #[ORM\OneToMany(targetEntity: Assignment::class, mappedBy: 'vehicle')]
    private Collection $assignments;

    /**
     * @var Collection<int, Trip>
     */
    #[ORM\OneToMany(targetEntity: Trip::class, mappedBy: 'vehicle')]
    private Collection $trips;

    /**
     * @var Collection<int, MaintenanceRecord>
     */
    #[ORM\OneToMany(targetEntity: MaintenanceRecord::class, mappedBy: 'vehicle')]
    private Collection $maintenanceRecords;

    /**
     * @var Collection<int, Refuel>
     */
    #[ORM\OneToMany(targetEntity: Refuel::class, mappedBy: 'vehicle')]
    private Collection $refuels;

    public function __construct()
    {
        $this->assignments = new ArrayCollection();
        $this->trips = new ArrayCollection();
        $this->maintenanceRecords = new ArrayCollection();
        $this->refuels = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVIN(): ?string
    {
        return $this->VIN;
    }

    public function setVIN(string $VIN): static
    {
        $this->VIN = $VIN;

        return $this;
    }

    public function getPlateNumber(): ?string
    {
        return $this->plateNumber;
    }

    public function setPlateNumber(string $plateNumber): static
    {
        $this->plateNumber = $plateNumber;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getMileage(): ?int
    {
        return $this->mileage;
    }

    public function setMileage(int $mileage): static
    {
        $this->mileage = $mileage;

        return $this;
    }

    /**
     * @return Collection<int, Assignment>
     */
    public function getAssignments(): Collection
    {
        return $this->assignments;
    }

    public function addAssignment(Assignment $assignment): static
    {
        if (!$this->assignments->contains($assignment)) {
            $this->assignments->add($assignment);
            $assignment->setVehicle($this);
        }

        return $this;
    }

    public function removeAssignment(Assignment $assignment): static
    {
        if ($this->assignments->removeElement($assignment)) {
            // set the owning side to null (unless already changed)
            if ($assignment->getVehicle() === $this) {
                $assignment->setVehicle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Trip>
     */
    public function getTrips(): Collection
    {
        return $this->trips;
    }

    public function addTrip(Trip $trip): static
    {
        if (!$this->trips->contains($trip)) {
            $this->trips->add($trip);
            $trip->setVehicle($this);
        }

        return $this;
    }

    public function removeTrip(Trip $trip): static
    {
        if ($this->trips->removeElement($trip)) {
            // set the owning side to null (unless already changed)
            if ($trip->getVehicle() === $this) {
                $trip->setVehicle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MaintenanceRecord>
     */
    public function getMaintenanceRecords(): Collection
    {
        return $this->maintenanceRecords;
    }

    public function addMaintenanceRecord(MaintenanceRecord $maintenanceRecord): static
    {
        if (!$this->maintenanceRecords->contains($maintenanceRecord)) {
            $this->maintenanceRecords->add($maintenanceRecord);
            $maintenanceRecord->setVehicle($this);
        }

        return $this;
    }

    public function removeMaintenanceRecord(MaintenanceRecord $maintenanceRecord): static
    {
        if ($this->maintenanceRecords->removeElement($maintenanceRecord)) {
            // set the owning side to null (unless already changed)
            if ($maintenanceRecord->getVehicle() === $this) {
                $maintenanceRecord->setVehicle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Refuel>
     */
    public function getRefuels(): Collection
    {
        return $this->refuels;
    }

    public function addRefuel(Refuel $refuel): static
    {
        if (!$this->refuels->contains($refuel)) {
            $this->refuels->add($refuel);
            $refuel->setVehicle($this);
        }

        return $this;
    }

    public function removeRefuel(Refuel $refuel): static
    {
        if ($this->refuels->removeElement($refuel)) {
            // set the owning side to null (unless already changed)
            if ($refuel->getVehicle() === $this) {
                $refuel->setVehicle(null);
            }
        }

        return $this;
    }
}
