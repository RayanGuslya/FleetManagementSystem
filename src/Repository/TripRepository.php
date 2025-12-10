<?php

namespace App\Repository;

use App\Entity\Trip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trip>
 */
class TripRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trip::class);
    }

    //    /**
    //     * @return Trip[] Returns an array of Trip objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Trip
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function getVehicleMileage(int $vehicleId): float
    {
        return (float)$this->createQueryBuilder('t')
            ->select('SUM(t.kilometers)')
            ->where('t.vehicle = :vehicle')
            ->setParameter('vehicle', $vehicleId)
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function findFuelAnomalies(int $vehicleId): array
    {
        $trips = $this->createQueryBuilder('t')
            ->where('t.vehicle = :vehicle')
            ->andWhere('t.fuelUsed > 0')
            ->andWhere('t.kilometers > 0')
            ->setParameter('vehicle', $vehicleId)
            ->orderBy('t.date', 'ASC')
            ->getQuery()
            ->getResult();

        if (count($trips) < 5) {
            return []; // недостаточно данных
        }

        $consumptions = [];
        foreach ($trips as $trip) {
            $consumptions[] = ($trip->getFuelUsed() / $trip->getKilometers()) * 100;
        }

        $mean = array_sum($consumptions) / count($consumptions);

        // вычисляем стандартное отклонение
        $variance = 0;
        foreach ($consumptions as $value) {
            $variance += pow($value - $mean, 2);
        }
        $stddev = sqrt($variance / count($consumptions));

        $threshold = $mean + 2 * $stddev;

        // выбираем аномалии
        $anomalies = [];
        foreach ($trips as $trip) {
            $c = ($trip->getFuelUsed() / $trip->getKilometers()) * 100;
            if ($c > $threshold) {
                $anomalies[] = [
                    'trip' => $trip,
                    'consumption' => $c
                ];
            }
        }

        return $anomalies;
    }

    public function getSummaryPerVehicle(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
            SELECT v.id as vehicle_id, v.model,
                   SUM(t.kilometers) AS total_km,
                   SUM(t.fuel_used) AS total_fuel
            FROM trip t
            JOIN vehicle v ON v.id = t.vehicle_id
            GROUP BY v.id, v.model
            ORDER BY total_km DESC
        ";
        return $conn->fetchAllAssociative($sql);
    }

}
