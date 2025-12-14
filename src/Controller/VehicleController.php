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
            $vin = $request->request->get('VIN');
            $plateNumber = $request->request->get('plateNumber');
            $model = $request->request->get('model');
            $status = $request->request->get('status');
            
            $vehicle->setVIN(is_string($vin) ? trim($vin) : '');
            $vehicle->setPlateNumber(is_string($plateNumber) ? trim($plateNumber) : '');
            $vehicle->setModel(is_string($model) ? trim($model) : '');
            $vehicle->setStatus(is_string($status) ? trim($status) : '');
            

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
            $vin = $request->request->get('vin');
            $plateNumber = $request->request->get('plateNumber');
            $model = $request->request->get('model');
            $status = $request->request->get('status');
            
            $vehicle->setVIN(is_string($vin) ? trim($vin) : '');
            $vehicle->setPlateNumber(is_string($plateNumber) ? trim($plateNumber) : '');
            $vehicle->setModel(is_string($model) ? trim($model) : '');
            $vehicle->setStatus(is_string($status) ? trim($status) : '');
            

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
