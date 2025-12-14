<?php

namespace App\Repository;

use App\Entity\Vehicle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicle>
 */
class VehicleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }

    /**
     * Возвращает рейтинг автомобилей по затратам на топливо (при цене 60 руб/л)
     *
     * @return array<int, array{
     *     id: int,
     *     model: string,
     *     total_fuel: float,
     *     total_cost: float
     * }>
     */
    public function getVehiclesFuelCostRating(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT
                v.id,
                v.model,
                COALESCE(SUM(t.fuel_used), 0) AS total_fuel,
                COALESCE(SUM(t.fuel_used * 60), 0) AS total_cost
            FROM vehicle v
            LEFT JOIN trip t ON v.id = t.vehicle_id
            GROUP BY v.id, v.model
            ORDER BY total_cost DESC
        ";

        return $conn->fetchAllAssociative($sql);
    }
}
