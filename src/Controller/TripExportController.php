<?php

namespace App\Controller;

use App\Repository\TripRepository;
use App\Repository\VehicleRepository;
use App\Service\TripImportService;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class TripExportController extends AbstractController
{
    #[Route('/trips/import', name: 'trips_import', methods: ['GET', 'POST'])]
    public function import(Request $request, TripImportService $importService)
    {
        if ($request->isMethod('POST')) {
            $file = $request->files->get('csv_file');
            if (!$file) {
                $this->addFlash('error', 'Не выбран CSV файл');
                return $this->redirectToRoute('trip_index');
            }

            $stream = fopen($file->getPathname(), 'r');
            $result = $importService->importCsvStream($stream);
            fclose($stream);

            $this->addFlash('success', sprintf('Импортировано %d, Пропущено %d', $result['imported'], $result['skipped']));
            if (!empty($result['errors'])) {
                $this->addFlash('warning', implode('; ', array_slice($result['errors'], 0, 5)));
            }
            return $this->redirectToRoute('trip_index');
        }

        return $this->render('trip/import.html.twig');
    }

    #[Route('/trips/export/csv', name: 'trips_export_csv')]
    public function exportCsv(TripRepository $tripRepo)
    {
        $trips = $tripRepo->findAll();

        $response = new StreamedResponse(function () use ($trips) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'date', 'kilometers', 'fuelUsed', 'vehicleId', 'driverId']);
            foreach ($trips as $t) {
                fputcsv($handle, [
                    $t->getId(),
                    $t->getDate(),
                    $t->getKilometers(),
                    $t->getFuelUsed(),
                    $t->getVehicle()?->getId(),
                    $t->getDriver()?->getId(),
                ]);
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="trips_' . date('Ymd_His') . '.csv"');

        return $response;
    }

    #[Route('/trips/export/xlsx', name: 'trips_export_xlsx')]
    public function exportXlsx(TripRepository $tripRepo): Response
    {
        $trips = $tripRepo->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            'ID', 'Дата', 'Километры', 'Топливо (л)', 'Автомобиль', 'Госномер', 'Водитель'
        ], null, 'A1');

        $row = 2;
        foreach ($trips as $t) {
            $sheet->fromArray([
                $t->getId(),
                $t->getDate(),
                $t->getKilometers(),
                $t->getFuelUsed(),
                $t->getVehicle()?->getModel() ?? '',
                $t->getVehicle()?->getPlateNumber() ?? '',
                $t->getDriver()?->getName() ?? '',
            ], null, "A$row");
            $row++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'trips_') . '.xlsx';
        $writer->save($tempFile);

        return $this->file($tempFile, 'trips_' . date('Ymd_His') . '.xlsx');
    }


    #[Route('/reports/summary/csv', name: 'report_summary_csv')]
    public function reportSummaryCsv(TripRepository $tripRepo)
    {
        $rows = $tripRepo->getSummaryPerVehicle();

        $response = new StreamedResponse(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['vehicleId', 'model', 'total_km', 'total_fuel', 'avg_km_per_l']);
            foreach ($rows as $r) {
                fputcsv($handle, [
                    $r['vehicle_id'],
                    $r['model'],
                    $r['total_km'],
                    $r['total_fuel'],
                    round(($r['total_km'] / max(1, $r['total_fuel'])), 2)
                ]);
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="report_summary_' . date('Ymd_His') . '.csv"');

        return $response;
    }

    #[Route('/reports/summary/pdf', name: 'report_summary_pdf')]
    public function reportSummaryPdf(TripRepository $tripRepo): Response
    {
        $rows = $tripRepo->getSummaryPerVehicle();
    
        $html = $this->renderView('reports/summary_pdf.html.twig', [
            'rows' => $rows,
            'generatedAt' => new \DateTime(),
        ]);

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('isPhpEnabled', false);
    
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
    
        $pdfContent = $dompdf->output();
    
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="report_summary_' . date('Ymd_His') . '.pdf"',
        ]);
    }
}
