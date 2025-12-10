<?php

namespace App\Controller;

use App\Entity\Route; // сущность маршрута
use App\Repository\RouteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route as RouteAttr; // <- алиас

class RouteMapController extends AbstractController
{
    #[RouteAttr('/routes/map/{id}', name: 'route_map')]
    public function map(int $id, RouteRepository $routeRepository): Response
    {
        $route = $routeRepository->find($id);

        if (!$route) {
            throw $this->createNotFoundException("Маршрут не найден");
        }

        return $this->render('route/map.html.twig', [
            'yandex_api_key' => $_ENV['YANDEX_MAP_KEY'],

            'start_lat' => $route->getStartLat(),
            'start_lng' => $route->getStartLng(),

            'end_lat'   => $route->getEndLat(),
            'end_lng'   => $route->getEndLng(),
        ]);
    }
}
