<?php

namespace App\Controller;

use App\Entity\Assignment;
use App\Repository\AssignmentRepository;
use App\Repository\DriverRepository;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AssignmentController extends AbstractController
{
    #[Route('/assignments', name: 'assignments_list')]
    public function index(AssignmentRepository $assignmentRepository): Response
    {
        $assignments = $assignmentRepository->findAll();

        return $this->render('assignments/index.html.twig', [
            'assignments' => $assignments,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeAssignment(Assignment $a): array
    {
        return [
            'id' => $a->getId(),
            'startDate' => $a->getStartDate(),
            'endDate' => $a->getEndDate(),
            'driver' => $a->getDriver()?->getName(),
            'vehicle' => $a->getVehicle()?->getModel(),
        ];
    }

    #[Route('/assignments/create', name: 'assignments_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        DriverRepository $driverRepo,
        VehicleRepository $vehicleRepo,
        LoggerInterface $auditLogger
    ): Response {
        if ($request->isMethod('POST')) {
            $assignment = new Assignment();

            $startDate = $request->request->get('startDate');
            $endDate = $request->request->get('endDate');

            // Защита: приводим к строке, если null — пустая строка
            $assignment->setStartDate(is_string($startDate) ? $startDate : '');
            $assignment->setEndDate(is_string($endDate) ? $endDate : '');

            $driverId = $request->request->get('driver');
            $vehicleId = $request->request->get('vehicle');

            $driver = $driverId ? $driverRepo->find($driverId) : null;
            $vehicle = $vehicleId ? $vehicleRepo->find($vehicleId) : null;

            $assignment->setDriver($driver);
            $assignment->setVehicle($vehicle);

            $em->persist($assignment);
            $em->flush();

            $auditLogger->info('Assignment created', [
                'username' => $this->getUser()?->getUserIdentifier() ?? 'anonymous',
                'action' => 'create',
                'entity' => Assignment::class,
                'entityId' => $assignment->getId(),
                'diff' => json_encode([
                    'after' => $this->serializeAssignment($assignment)
                ]),
            ]);

            return $this->redirectToRoute('assignments_list');
        }

        return $this->render('assignments/create.html.twig', [
            'drivers' => $driverRepo->findAll(),
            'vehicles' => $vehicleRepo->findAll(),
        ]);
    }

    #[Route('/assignments/edit/{id}', name: 'assignments_edit', methods: ['GET', 'POST'])]
    public function edit(
        int $id,
        Request $request,
        AssignmentRepository $assignmentRepo,
        DriverRepository $driverRepo,
        VehicleRepository $vehicleRepo,
        EntityManagerInterface $em,
        LoggerInterface $auditLogger
    ): Response {
        $assignment = $assignmentRepo->find($id);

        if (!$assignment) {
            throw $this->createNotFoundException('Assignment not found');
        }

        $oldData = clone $assignment;

        if ($request->isMethod('POST')) {
            $startDate = $request->request->get('startDate');
            $endDate = $request->request->get('endDate');

            $assignment->setStartDate(is_string($startDate) ? $startDate : '');
            $assignment->setEndDate(is_string($endDate) ? $endDate : '');

            $driverId = $request->request->get('driver');
            $vehicleId = $request->request->get('vehicle');

            $driver = $driverId ? $driverRepo->find($driverId) : null;
            $vehicle = $vehicleId ? $vehicleRepo->find($vehicleId) : null;

            $assignment->setDriver($driver);
            $assignment->setVehicle($vehicle);

            $em->flush();

            $auditLogger->info('Assignment updated', [
                'username' => $this->getUser()?->getUserIdentifier() ?? 'anonymous',
                'action' => 'update',
                'entity' => Assignment::class,
                'entityId' => $assignment->getId(),
                'diff' => json_encode([
                    'before' => $this->serializeAssignment($oldData),
                    'after' => $this->serializeAssignment($assignment),
                ]),
            ]);

            return $this->redirectToRoute('assignments_list');
        }

        return $this->render('assignments/edit.html.twig', [
            'assignment' => $assignment,
            'drivers' => $driverRepo->findAll(),
            'vehicles' => $vehicleRepo->findAll(),
        ]);
    }

    #[Route('/assignments/delete/{id}', name: 'assignments_delete', methods: ['POST'])]
    public function delete(
        int $id,
        AssignmentRepository $repo,
        EntityManagerInterface $em,
        LoggerInterface $auditLogger
    ): Response {
        $assignment = $repo->find($id);

        if (!$assignment) {
            // Если не найден — просто редиректим, без ошибки
            return $this->redirectToRoute('assignments_list');
        }

        $serialized = $this->serializeAssignment($assignment);

        $em->remove($assignment);
        $em->flush();

        $auditLogger->info('Assignment deleted', [
            'username' => $this->getUser()?->getUserIdentifier() ?? 'anonymous',
            'action' => 'delete',
            'entity' => Assignment::class,
            'entityId' => $id,
            'diff' => json_encode([
                'before' => $serialized
            ]),
        ]);

        return $this->redirectToRoute('assignments_list');
    }
}
