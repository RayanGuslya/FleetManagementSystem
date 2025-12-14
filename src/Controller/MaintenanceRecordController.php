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

    /**
     * @return array<string, mixed>
     */
    private function serializeMaintenance(MaintenanceRecord $m): array
    {
        return [
            'id' => $m->getId(),
            'vehicle_id' => $m->getVehicle()?->getId(),
            'date' => $m->getDate(),
            'work_type' => $m->getWorkType(),
            'cost' => $m->getCost(),
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

            $date = $request->request->get('date');
            $workType = $request->request->get('workType');
            $cost = $request->request->get('cost');
            $vehicleId = $request->request->get('vehicle');

            $record->setDate(is_string($date) ? trim($date) : '');
            $record->setWorkType(is_string($workType) ? trim($workType) : '');
            $record->setCost(is_numeric($cost) ? (float)$cost : 0.0);

            $vehicle = $vehicleId ? $vehicleRepo->find($vehicleId) : null;
            $record->setVehicle($vehicle);

            $em->persist($record);
            $em->flush();

            $auditLogger->info('MaintenanceRecord created', [
                'username' => $this->getUser()?->getUserIdentifier() ?? 'anonymous',
                'action' => 'create',
                'entity' => MaintenanceRecord::class,
                'entityId' => $record->getId(),
                'diff' => json_encode(['after' => $this->serializeMaintenance($record)]),
            ]);

            $this->addFlash('success', 'Запись о ТО успешно добавлена.');
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
            throw $this->createNotFoundException('Запись ТО не найдена');
        }

        $oldData = clone $record;

        if ($request->isMethod('POST')) {
            $date = $request->request->get('date');
            $workType = $request->request->get('workType');
            $cost = $request->request->get('cost');
            $vehicleId = $request->request->get('vehicle');

            $record->setDate(is_string($date) ? trim($date) : '');
            $record->setWorkType(is_string($workType) ? trim($workType) : '');
            $record->setCost(is_numeric($cost) ? (float)$cost : 0.0);

            $vehicle = $vehicleId ? $vehicleRepo->find($vehicleId) : null;
            $record->setVehicle($vehicle);

            $em->flush();

            $auditLogger->info('MaintenanceRecord updated', [
                'username' => $this->getUser()?->getUserIdentifier() ?? 'anonymous',
                'action' => 'update',
                'entity' => MaintenanceRecord::class,
                'entityId' => $record->getId(),
                'diff' => json_encode([
                    'before' => $this->serializeMaintenance($oldData),
                    'after' => $this->serializeMaintenance($record),
                ]),
            ]);

            $this->addFlash('success', 'Запись о ТО обновлена.');
            return $this->redirectToRoute('maintenance_list');
        }

        return $this->render('maintenance/edit.html.twig', [
            'record' => $record,
            'vehicles' => $vehicleRepo->findAll(),
        ]);
    }

    #[Route('/maintenance/delete/{id}', name: 'maintenance_delete', methods: ['POST'])]
    public function delete(
        int $id,
        MaintenanceRecordRepository $repo,
        EntityManagerInterface $em,
        LoggerInterface $auditLogger
    ): Response {
        $record = $repo->find($id);

        if (!$record) {
            $this->addFlash('warning', 'Запись о ТО не найдена.');
            return $this->redirectToRoute('maintenance_list');
        }

        $serialized = $this->serializeMaintenance($record);

        $em->remove($record);
        $em->flush();

        $auditLogger->info('MaintenanceRecord deleted', [
            'username' => $this->getUser()?->getUserIdentifier() ?? 'anonymous',
            'action' => 'delete',
            'entity' => MaintenanceRecord::class,
            'entityId' => $id,
            'diff' => json_encode(['before' => $serialized]),
        ]);

        $this->addFlash('success', 'Запись о ТО удалена.');
        return $this->redirectToRoute('maintenance_list');
    }
}
