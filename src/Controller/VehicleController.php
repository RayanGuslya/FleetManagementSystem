<?php

namespace App\Controller;

use App\Entity\Vehicle;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VehicleController extends AbstractController
{
    #[Route('/vehicles', name: 'vehicle_index', methods: ['GET'])]
    public function index(VehicleRepository $vehicleRepository): Response
    {
        $vehicles = $vehicleRepository->findAll();

        return $this->render('vehicle/index.html.twig', [
            'vehicles' => $vehicles,
        ]);
    }

    #[Route('/create', name: 'vehicle_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $vehicle = new Vehicle();

        if ($request->isMethod('POST')) {
            $vehicle->setVIN($request->request->get('VIN'));
            $vehicle->setPlateNumber($request->request->get('plateNumber'));
            $vehicle->setModel($request->request->get('model'));
            $vehicle->setStatus($request->request->get('status'));
            $vehicle->setMileage((int) $request->request->get('mileage'));

            $em->persist($vehicle);
            $em->flush();

            return $this->redirectToRoute('vehicle_index');
        }

        return $this->render('vehicle/create.html.twig');
    }

    #[Route('/{id}/edit', name: 'vehicle_edit', methods: ['GET', 'POST'])]
    public function edit(Vehicle $vehicle, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $vehicle->setVIN($request->request->get('VIN'));
            $vehicle->setPlateNumber($request->request->get('plateNumber'));
            $vehicle->setModel($request->request->get('model'));
            $vehicle->setStatus($request->request->get('status'));
            $vehicle->setMileage((int) $request->request->get('mileage'));

            $em->flush();

            return $this->redirectToRoute('vehicle_index');
        }

        return $this->render('vehicle/edit.html.twig', [
            'vehicle' => $vehicle,
        ]);
    }

    #[Route('/{id}/delete', name: 'vehicle_delete', methods: ['POST'])]
    public function delete(Vehicle $vehicle, EntityManagerInterface $em): Response
    {
        $em->remove($vehicle);
        $em->flush();

        return $this->redirectToRoute('vehicle_index');
    }
}
