<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Repository\DriverRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DriverController extends AbstractController
{
    #[Route('/drivers', name: 'drivers_list')]
    public function index(DriverRepository $driverRepository): Response
    {
        $drivers = $driverRepository->findAll();

        return $this->render('drivers/index.html.twig', [
            'drivers' => $drivers,
        ]);
    }

    #[Route('/drivers/create', name: 'drivers_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $driver = new Driver();
            $driver->setName($request->request->get('name'));
            $driver->setLicenseCategory($request->request->get('licenseCategory'));
            $driver->setContact($request->request->get('contact'));

            $em->persist($driver);
            $em->flush();

            return $this->redirectToRoute('drivers_list');
        }

        return $this->render('drivers/create.html.twig');
    }

    #[Route('/drivers/edit/{id}', name: 'drivers_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, DriverRepository $repo, EntityManagerInterface $em): Response
    {
        $driver = $repo->find($id);

        if (!$driver) {
            throw $this->createNotFoundException("Driver not found");
        }

        if ($request->isMethod('POST')) {
            $driver->setName($request->request->get('name'));
            $driver->setLicenseCategory($request->request->get('licenseCategory'));
            $driver->setContact($request->request->get('contact'));

            $em->flush();

            return $this->redirectToRoute('drivers_list');
        }

        return $this->render('drivers/edit.html.twig', [
            'driver' => $driver
        ]);
    }

    #[Route('/drivers/delete/{id}', name: 'drivers_delete', methods: ['POST'])]
    public function delete(int $id, DriverRepository $repo, EntityManagerInterface $em): Response
    {
        $driver = $repo->find($id);

        if ($driver) {
            $em->remove($driver);
            $em->flush();
        }

        return $this->redirectToRoute('drivers_list');
    }
}
