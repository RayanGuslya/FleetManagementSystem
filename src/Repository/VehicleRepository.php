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

//    /**
//     * @return Vehicle[] Returns an array of Vehicle objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Vehicle
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
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
            GROUP BY v.id
            ORDER BY total_cost DESC
        ";
    
        return $conn->fetchAllAssociative($sql);
    }

}
