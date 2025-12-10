<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Repository\TripRepository;
use App\Repository\VehicleRepository;
use App\Repository\DriverRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route as RouteAttribute;

class TripController extends AbstractController
{
    #[RouteAttribute('/trips', name: 'trip_index')]
    public function index(TripRepository $tripRepository): Response
    {
        $user = $this->getUser();
    
        if (in_array('ROLE_DRIVER', $user->getRoles())) {
            $trips = $tripRepository->findBy(['driver' => $user]);
        } else {
            $trips = $tripRepository->findAll();
        }
    
        return $this->render('trip/index.html.twig', [
            'trips' => $trips,
        ]);
    }
    
    #[RouteAttribute('/trips/create', name: 'trip_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        VehicleRepository $vehicleRepository,
        DriverRepository $driverRepository
    ): Response {
        $vehicles = $vehicleRepository->findAll();
        $drivers = $driverRepository->findAll();

        if ($request->isMethod('POST')) {
            $trip = new Trip();
            $trip->setDate($request->request->get('date'));
            $trip->setKilometers((int)$request->request->get('kilometers'));
            $trip->setFuelUsed((float)$request->request->get('fuelUsed'));

            $vehicle = $vehicleRepository->find((int)$request->request->get('vehicle'));
            $driver = $driverRepository->find((int)$request->request->get('driver'));

            $trip->setVehicle($vehicle);
            $trip->setDriver($driver);

            $em->persist($trip);
            $em->flush();

            return $this->redirectToRoute('trip_index');
        }

        return $this->render('trip/create.html.twig', [
            'vehicles' => $vehicles,
            'drivers' => $drivers,
        ]);
    }

    #[RouteAttribute('/trips/edit/{id}', name: 'trip_edit', methods: ['GET', 'POST'])]
    public function edit(
        int $id,
        Request $request,
        TripRepository $tripRepository,
        VehicleRepository $vehicleRepository,
        DriverRepository $driverRepository,
        EntityManagerInterface $em
    ): Response {
        $trip = $tripRepository->find($id);
        if (!$trip) {
            throw $this->createNotFoundException('Поездка не найдена');
        }

        $vehicles = $vehicleRepository->findAll();
        $drivers = $driverRepository->findAll();

        if ($request->isMethod('POST')) {
            $trip->setDate($request->request->get('date'));
            $trip->setKilometers((int)$request->request->get('kilometers'));
            $trip->setFuelUsed((float)$request->request->get('fuelUsed'));

            $vehicle = $vehicleRepository->find((int)$request->request->get('vehicle'));
            $driver = $driverRepository->find((int)$request->request->get('driver'));

            $trip->setVehicle($vehicle);
            $trip->setDriver($driver);

            $em->flush();

            return $this->redirectToRoute('trip_index');
        }

        return $this->render('trip/edit.html.twig', [
            'trip' => $trip,
            'vehicles' => $vehicles,
            'drivers' => $drivers,
        ]);
    }

    #[RouteAttribute('/trips/delete/{id}', name: 'trip_delete')]
    public function delete(int $id, TripRepository $tripRepository, EntityManagerInterface $em): Response
    {
        $trip = $tripRepository->find($id);
        if ($trip) {
            $em->remove($trip);
            $em->flush();
        }

        return $this->redirectToRoute('trip_index');
    }
}
