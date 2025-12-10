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
            'records' => $records
        ]);
    }

    #[Route('/new', name: 'refuel_create')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $vehicles = $em->getRepository(Vehicle::class)->findAll();

        if ($request->isMethod('POST')) {
            $refuel = new Refuel();
            $refuel->setDate($request->request->get('date'));
            $refuel->setLiters($request->request->get('liters'));
            $refuel->setAmount($request->request->get('amount'));

            $vehicle = $em->getRepository(Vehicle::class)->find($request->request->get('vehicle'));
            $refuel->setVehicle($vehicle);

            $em->persist($refuel);
            $em->flush();

            return $this->redirectToRoute('refuel_list');
        }

        return $this->render('refuel/create.html.twig', [
            'vehicles' => $vehicles
        ]);
    }

    #[Route('/edit/{id}', name: 'refuel_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $record = $em->getRepository(Refuel::class)->find($id);
        $vehicles = $em->getRepository(Vehicle::class)->findAll();

        if (!$record) {
            throw $this->createNotFoundException('Заправка не найдена');
        }

        if ($request->isMethod('POST')) {
            $record->setDate($request->request->get('date'));
            $record->setLiters($request->request->get('liters'));
            $record->setAmount($request->request->get('amount'));

            $vehicle = $em->getRepository(Vehicle::class)->find($request->request->get('vehicle'));
            $record->setVehicle($vehicle);

            $em->flush();

            return $this->redirectToRoute('refuel_list');
        }

        return $this->render('refuel/edit.html.twig', [
            'record' => $record,
            'vehicles' => $vehicles
        ]);
    }

    #[Route('/delete/{id}', name: 'refuel_delete')]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $record = $em->getRepository(Refuel::class)->find($id);

        if ($record) {
            $em->remove($record);
            $em->flush();
        }

        return $this->redirectToRoute('refuel_list');
    }
}
