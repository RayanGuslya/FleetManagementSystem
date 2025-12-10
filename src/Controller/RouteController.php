<?php

// src/Controller/RouteController.php

namespace App\Controller;

use App\Entity\Route;
use App\Repository\RouteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route as RouteAttribute;

class RouteController extends AbstractController
{
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($earthRadius * $c);
    }

    #[RouteAttribute('/routes', name: 'route_index')]
    public function index(RouteRepository $routeRepository): Response
    {
        return $this->render('route/index.html.twig', [
            'routes' => $routeRepository->findAll(),
        ]);
    }

    #[RouteAttribute('/route/create', name: 'route_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $route = new Route();

            $startLat = (float)$request->request->get('startLat');
            $startLng = (float)$request->request->get('startLng');
            $endLat = (float)$request->request->get('endLat');
            $endLng = (float)$request->request->get('endLng');

            $distance = $this->calculateDistance($startLat, $startLng, $endLat, $endLng);

            $route->setStartLat($startLat)
                  ->setStartLng($startLng)
                  ->setEndLat($endLat)
                  ->setEndLng($endLng)
                  ->setDistance($distance);

            $em->persist($route);
            $em->flush();

            $this->addFlash('success', 'Маршрут создан! Расстояние: ' . $distance . ' м');

            return $this->redirectToRoute('route_index');
        }

        return $this->render('route/create.html.twig');
    }

    #[RouteAttribute('/routes/edit/{id}', name: 'route_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, RouteRepository $routeRepository, EntityManagerInterface $em): Response
    {
        $route = $routeRepository->find($id);
        if (!$route) {
            throw $this->createNotFoundException('Маршрут не найден');
        }

        if ($request->isMethod('POST')) {
            $startLat = (float)$request->request->get('startLat');
            $startLng = (float)$request->request->get('startLng');
            $endLat = (float)$request->request->get('endLat');
            $endLng = (float)$request->request->get('endLng');

            $distance = $this->calculateDistance($startLat, $startLng, $endLat, $endLng);

            $route->setStartLat($startLat)
                  ->setStartLng($startLng)
                  ->setEndLat($endLat)
                  ->setEndLng($endLng)
                  ->setDistance($distance);

            $em->flush();

            $this->addFlash('success', 'Маршрут обновлён! Новое расстояние: ' . $distance . ' м');

            return $this->redirectToRoute('route_index');
        }

        return $this->render('route/edit.html.twig', ['route' => $route]);
    }

    #[RouteAttribute('/routes/delete/{id}', name: 'route_delete')]
    public function delete(int $id, RouteRepository $routeRepository, EntityManagerInterface $em): Response
    {
        $route = $routeRepository->find($id);
        if ($route) {
            $em->remove($route);
            $em->flush();
        }
        return $this->redirectToRoute('route_index');
    }
}
