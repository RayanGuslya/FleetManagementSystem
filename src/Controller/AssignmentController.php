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
            $assignment->setStartDate($request->request->get('startDate'));
            $assignment->setEndDate($request->request->get('endDate'));

            $driver = $driverRepo->find($request->request->get('driver'));
            $vehicle = $vehicleRepo->find($request->request->get('vehicle'));

            $assignment->setDriver($driver);
            $assignment->setVehicle($vehicle);

            $em->persist($assignment);
            $em->flush();

            $auditLogger->info('Assignment created', [
                'username' => implode(',', (array)($this->getUser()?->getRoles())),
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
            throw $this->createNotFoundException("Assignment not found");
        }
        
        $oldData = clone $assignment;

        if ($request->isMethod('POST')) {
            $assignment->setStartDate($request->request->get('startDate'));
            $assignment->setEndDate($request->request->get('endDate'));

            $driver = $driverRepo->find($request->request->get('driver'));
            $vehicle = $vehicleRepo->find($request->request->get('vehicle'));

            $assignment->setDriver($driver);
            $assignment->setVehicle($vehicle);

            $em->flush();

            $oldDataArr = $this->serializeAssignment($oldData);
            $afterArr = $this->serializeAssignment($assignment);
            
            $auditLogger->info('Assignment updated', [
                'username' => implode(',', (array)($this->getUser()?->getRoles())),
                'action' => 'update',
                'entity' => Assignment::class,
                'entityId' => $assignment->getId(),
                'diff' => json_encode([
                    'before' => $oldDataArr,
                    'after' => $afterArr,
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
    public function delete(int $id, AssignmentRepository $repo, EntityManagerInterface $em, LoggerInterface $auditLogger): Response
    {
        $assignment = $repo->find($id);

        if ($assignment) {
            $em->remove($assignment);
            $em->flush();
        }

        $auditLogger->info('Assignment deleted', [
            'username' => implode(',', (array)($this->getUser()?->getRoles())),
            'action' => 'delete',
            'entity' => Assignment::class,
            'entityId' => $assignment->getId(),
            'diff' => json_encode([
                'before' => $this->serializeAssignment($assignment)
            ]),
        ]);

        return $this->redirectToRoute('assignments_list');
    }
}
