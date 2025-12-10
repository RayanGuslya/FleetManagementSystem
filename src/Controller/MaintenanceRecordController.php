<?php

namespace App\Controller;

use App\Entity\MaintenanceRecord;
use App\Repository\MaintenanceRecordRepository;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MaintenanceRecordController extends AbstractController
{
    #[Route('/maintenance', name: 'maintenance_list')]
    public function index(MaintenanceRecordRepository $repo): Response
    {
        return $this->render('maintenance/index.html.twig', [
            'records' => $repo->findAll(),
        ]);
    }
  
    private function serializeMaintenance(MaintenanceRecord $m): array
    {
        return [
            'id' => $m->getId(),
            'vehicle_id' => $m->getVehicle(),
            'date' => $m->getDate(),
            'work_type' => $m->getWorkType(),
            'cost' => $m->getCost()
        ];
    }

    #[Route('/maintenance/create', name: 'maintenance_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        VehicleRepository $vehicleRepo,
        LoggerInterface $auditLogger
    ): Response {
        if ($request->isMethod('POST')) {
            $record = new MaintenanceRecord();

            $record->setDate($request->request->get('date'));
            $record->setWorkType($request->request->get('workType'));
            $record->setCost($request->request->get('cost'));

            $vehicle = $vehicleRepo->find($request->request->get('vehicle'));
            $record->setVehicle($vehicle);

            $em->persist($record);
            $em->flush();

            $auditLogger->info('MaintenanceRecord created', [
                'username' => implode(',', (array)($this->getUser()?->getRoles())),
                'action' => 'create',
                'entity' => MaintenanceRecord::class,
                'entityId' => $record->getId(),
                'diff' => json_encode([
                    'after' => $this->serializeMaintenance($record)
                ])
            ]);

            return $this->redirectToRoute('maintenance_list');
        }

        return $this->render('maintenance/create.html.twig', [
            'vehicles' => $vehicleRepo->findAll(),
        ]);
    }

    #[Route('/maintenance/edit/{id}', name: 'maintenance_edit', methods: ['GET', 'POST'])]
    public function edit(
        int $id,
        Request $request,
        MaintenanceRecordRepository $repo,
        VehicleRepository $vehicleRepo,
        EntityManagerInterface $em,
        LoggerInterface $auditLogger
    ): Response {
        $record = $repo->find($id);

        if (!$record) {
            throw $this->createNotFoundException("Запись ТО не найдена");
        }

        $oldData = clone $record;

        if ($request->isMethod('POST')) {
            $record->setDate($request->request->get('date'));
            $record->setWorkType($request->request->get('workType'));
            $record->setCost($request->request->get('cost'));

            $vehicle = $vehicleRepo->find($request->request->get('vehicle'));
            $record->setVehicle($vehicle);

            $em->flush();
            
            $oldDataArr = $this->serializeMaintenance($oldData);
            $afterArr = $this->serializeMaintenance($record);

            $auditLogger->info('MaintenanceRecord updated', [
                'username' => implode(',', (array)($this->getUser()?->getRoles())),
                'action' => 'update',
                'entity' => MaintenanceRecord::class,
                'entityId' => $record->getId(),
                'diff' => json_encode([
                    'before' => $oldDataArr,
                    'after' => $afterArr,
                ]),
            ]);

            return $this->redirectToRoute('maintenance_list');
        }

        return $this->render('maintenance/edit.html.twig', [
            'record' => $record,
            'vehicles' => $vehicleRepo->findAll(),
        ]);
    }

    #[Route('/maintenance/delete/{id}', name: 'maintenance_delete', methods: ['POST'])]
    public function delete(int $id, MaintenanceRecordRepository $repo, EntityManagerInterface $em, LoggerInterface $auditLogger): Response
    {
        $record = $repo->find($id);

        if ($record) {
            $em->remove($record);
            $em->flush();
        }

        $auditLogger->info('MaintenanceRecord deleted', [
            'username' => implode(',', (array)($this->getUser()?->getRoles())),
            'action' => 'delete',
            'entity' => MaintenanceRecord::class,
            'entityId' => $record->getId(),
            'diff' => json_encode([
                'after' => $this->serializeMaintenance($record)
            ]),
        ]);

        return $this->redirectToRoute('maintenance_list');
    }
}
