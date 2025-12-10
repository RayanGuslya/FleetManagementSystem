<?php

namespace App\Service;

use App\Entity\Trip;
use App\Repository\VehicleRepository;
use App\Repository\DriverRepository;
use Doctrine\ORM\EntityManagerInterface;

class TripImportService
{
    public function __construct(
        private EntityManagerInterface $em,
        private VehicleRepository $vehicleRepo,
        private DriverRepository $driverRepo
    ) {}

    /**
     * @param resource $stream
     * @return array{imported: int, skipped: int, errors: string[]}
     */
    public function importCsvStream($stream): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $header = null;
        $line = 0;

        $sample = stream_get_contents($stream, 1000);
        rewind($stream);
        $delimiter = str_contains($sample, ';') ? ';' : ',';

        while (($row = fgetcsv($stream, 0, $delimiter)) !== false) {
            $line++;

            if (empty(array_filter($row))) {
                continue;
            }

            if ($header === null) {
                $header = array_map('trim', array_map('strtolower', $row));
                $header = array_map(function ($h) {
                    return str_replace([' ', '_', '-'], '', $h);
                }, $header);
                continue;
            }

            $data = [];
            foreach ($header as $i => $key) {
                $data[$key] = $row[$i] ?? null;
            }

            $dateStr     = trim($data['date'] ?? $data['дата'] ?? '');
            $km          = (int) ($data['kilometers'] ?? $data['km'] ?? $data['километры'] ?? 0);
            $fuel        = (float) str_replace(',', '.', $data['fuelused'] ?? $data['fuel'] ?? $data['топливо'] ?? 0);
            $vehicleId   = (int) ($data['vehicleid'] ?? $data['vehicle'] ?? $data['автомобиль'] ?? 0);
            $driverId    = (int) ($data['driverid'] ?? $data['driver'] ?? $data['водитель'] ?? 0);

            if (!$dateStr || $km <= 0) {
                $errors[] = "Строка $line: неверные данные (дата или км)";
                $skipped++;
                continue;
            }

            $date = \DateTime::createFromFormat('Y-m-d', $dateStr)
                 ?: \DateTime::createFromFormat('d.m.Y', $dateStr)
                 ?: \DateTime::createFromFormat('d/m/Y', $dateStr);

            if (!$date) {
                $errors[] = "Строка $line: неверный формат даты '$dateStr'";
                $skipped++;
                continue;
            }

            $exists = $this->em->getRepository(Trip::class)->findOneBy([
                'date' => $date->format('Y-m-d'),
                'kilometers' => $km,
                'vehicle' => $vehicleId ?: null,
            ]);

            if ($exists) {
                $skipped++;
                continue;
            }

            $vehicle = $vehicleId ? $this->vehicleRepo->find($vehicleId) : null;
            $driver  = $driverId  ? $this->driverRepo->find($driverId)   : null;

            if ($vehicleId && !$vehicle) {
                $errors[] = "Строка $line: автомобиль не найден (ID=$vehicleId)";
                $skipped++;
                continue;
            }
            if ($driverId && !$driver) {
                $errors[] = "Строка $line: водитель не найден (ID=$driverId)";
                $skipped++;
                continue;
            }

            $trip = new Trip();
            $trip->setDate($date->format('Y-m-d'));
            $trip->setKilometers($km);
            $trip->setFuelUsed($fuel);
            if ($vehicle) $trip->setVehicle($vehicle);
            if ($driver)  $trip->setDriver($driver);

            $this->em->persist($trip);
            $imported++;
        }

        if ($imported > 0) {
            $this->em->flush();
        }

        return [
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ];
    }
}