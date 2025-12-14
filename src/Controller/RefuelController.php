<?php

namespace App\Controller;

use App\Entity\Refuel;
use App\Entity\Vehicle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RefuelController extends AbstractController
{
    #[Route('/refuel', name: 'refuel_list')]
    public function list(EntityManagerInterface $em): Response
    {
        $records = $em->getRepository(Refuel::class)->findAll();

        return $this->render('refuel/index.html.twig', [
            'records' => $records,
        ]);
    }

    #[Route('/refuel/create', name: 'refuel_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $vehicles = $em->getRepository(Vehicle::class)->findAll();

        if ($request->isMethod('POST')) {
            $refuel = new Refuel();

            $date = $request->request->get('date');
            $liters = $request->request->get('liters');
            $amount = $request->request->get('amount');
            $vehicleId = $request->request->get('vehicle');

            $refuel->setDate(is_string($date) ? trim($date) : '');
            $refuel->setLiters(is_numeric($liters) ? (float)$liters : 0.0);
            $refuel->setAmount(is_numeric($amount) ? (float)$amount : 0.0);

            $vehicle = $vehicleId ? $em->getRepository(Vehicle::class)->find($vehicleId) : null;
            $refuel->setVehicle($vehicle);

            $em->persist($refuel);
            $em->flush();

            $this->addFlash('success', 'Заправка успешно добавлена.');
            return $this->redirectToRoute('refuel_list');
        }

        return $this->render('refuel/create.html.twig', [
            'vehicles' => $vehicles,
        ]);
    }

    #[Route('/refuel/edit/{id}', name: 'refuel_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $record = $em->getRepository(Refuel::class)->find($id);
        $vehicles = $em->getRepository(Vehicle::class)->findAll();

        if (!$record) {
            throw $this->createNotFoundException('Заправка не найдена');
        }

        if ($request->isMethod('POST')) {
            $date = $request->request->get('date');
            $liters = $request->request->get('liters');
            $amount = $request->request->get('amount');
            $vehicleId = $request->request->get('vehicle');

            $record->setDate(is_string($date) ? trim($date) : '');
            $record->setLiters(is_numeric($liters) ? (float)$liters : 0.0);
            $record->setAmount(is_numeric($amount) ? (float)$amount : 0.0);

            $vehicle = $vehicleId ? $em->getRepository(Vehicle::class)->find($vehicleId) : null;
            $record->setVehicle($vehicle);

            $em->flush();

            $this->addFlash('success', 'Заправка обновлена.');
            return $this->redirectToRoute('refuel_list');
        }

        return $this->render('refuel/edit.html.twig', [
            'record' => $record,
            'vehicles' => $vehicles,
        ]);
    }

    #[Route('/refuel/delete/{id}', name: 'refuel_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $record = $em->getRepository(Refuel::class)->find($id);

        if ($record) {
            $em->remove($record);
            $em->flush();
            $this->addFlash('success', 'Заправка удалена.');
        } else {
            $this->addFlash('warning', 'Заправка не найдена.');
        }

        return $this->redirectToRoute('refuel_list');
    }
}
