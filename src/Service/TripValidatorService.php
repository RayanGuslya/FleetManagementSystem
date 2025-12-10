<?php
namespace App\Service;

use App\Repository\TripRepository;

class TripValidatorService
{
    public function __construct(private TripRepository $repo) {}

    public function isDuplicate(\DateTimeInterface $date, int $driverId): bool
    {
        return $this->repo->findOneBy([
            'date' => $date,
            'driver' => $driverId,
        ]) !== null;
    }
}
