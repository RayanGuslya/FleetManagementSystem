<?php

namespace App\Controller;

use App\Repository\TripRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/analytics', name: 'analytics_')]
class AnalyticsController extends AbstractController
{
    #[Route('/distance', name: 'distance')]
    public function totalDistance(EntityManagerInterface $em): Response
    {
        $result = $em->getConnection()->fetchAllAssociative("
        SELECT vehicle_id, SUM(kilometers) AS total_km
        FROM trip
        GROUP BY vehicle_id
    ");
    

        return $this->render('analytics/distance.html.twig', [
            'data' => $result
        ]);
    }

    #[Route('/anomalies', name: 'anomalies')]
    public function anomalies(EntityManagerInterface $em): Response
    {
        $result = $em->getConnection()->fetchAllAssociative("
            SELECT id, date, kilometers, fuel_used,
                   (kilometers / NULLIF(fuel_used, 0)) AS efficiency
            FROM trip
            WHERE (kilometers / NULLIF(fuel_used, 0)) > 25
               OR (kilometers / NULLIF(fuel_used, 0)) < 3
        ");

        return $this->render('analytics/anomalies.html.twig', [
            'data' => $result
        ]);
    }

    #[Route('/rating', name: 'rating')]
    public function rating(EntityManagerInterface $em): Response
    {
        $result = $em->getConnection()->fetchAllAssociative("
            SELECT v.id, v.model,
                   SUM(r.amount) AS total_costs
            FROM vehicle v
            LEFT JOIN refuel r ON r.vehicle_id = v.id
            GROUP BY v.id, v.model
            ORDER BY total_costs DESC
        ");

        return $this->render('analytics/rating.html.twig', [
            'data' => $result
        ]);
    }
}
