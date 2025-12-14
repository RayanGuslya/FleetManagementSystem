<?php

namespace App\Controller;

use App\Repository\TripRepository;
use App\Service\TripImportService;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class TripExportController extends AbstractController
{
    #[Route('/trips/import', name: 'trips_import', methods: ['GET', 'POST'])]
    public function import(Request $request, TripImportService $importService): Response
    {
        if ($request->isMethod('POST')) {
            $file = $request->files->get('csv_file');

            if (!$file instanceof UploadedFile) {
                $this->addFlash('error', 'Не выбран CSV файл');
                return $this->redirectToRoute('trip_index');
            }

            $stream = fopen($file->getPathname(), 'r');
            if ($stream === false) {
                throw new \RuntimeException('Не удалось открыть файл');
            }

            $result = $importService->importCsvStream($stream);
            fclose($stream);

            $this->addFlash(
                'success',
                sprintf('Импортировано %d, Пропущено %d', $result['imported'], $result['skipped'])
            );

            if (!empty($result['errors'])) {
                $this->addFlash('warning', implode('; ', array_slice($result['errors'], 0, 5)));
            }

            return $this->redirectToRoute('trip_index');
        }

        return $this->render('trip/import.html.twig');
    }

    #[Route('/trips/export/xlsx', name: 'trips_export_xlsx')]
    public function exportXlsx(TripRepository $tripRepo): Response
    {
        $trips = $tripRepo->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            'ID',
            'Дата',
            'Километры',
            'Топливо (л)',
            'Автомобиль (модель)',
            'Госномер',
            'Водитель'
        ], null, 'A1');

        $row = 2;
        foreach ($trips as $trip) {
            $sheet->fromArray([
                $trip->getId(),
                $trip->getDate() ? date('d.m.Y', strtotime($trip->getDate())) : '',
                $trip->getKilometers(),
                $trip->getFuelUsed(),
                $trip->getVehicle()?->getModel() ?? '',
                $trip->getVehicle()?->getPlateNumber() ?? '',
                $trip->getDriver()?->getName() ?? '',
            ], null, "A$row");
            $row++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'trips_') . '.xlsx';
        $writer->save($tempFile);

        return $this->file($tempFile, 'trips_' . date('Y-m-d_His') . '.xlsx');
    }

    #[Route('/trips/export/csv', name: 'trips_export_csv')]
    public function exportCsv(TripRepository $tripRepo): Response
    {
        $trips = $tripRepo->findAll();

        return new StreamedResponse(function () use ($trips): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

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
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="trips_' . date('Ymd_His') . '.csv"',
        ]);
    }

    #[Route('/reports/summary/csv', name: 'report_summary_csv')]
    public function reportSummaryCsv(TripRepository $tripRepo): Response
    {
        /**
         * @var array<int, array{
         *     vehicle_id:int,
         *     model:string,
         *     total_km:float,
         *     total_fuel:float
         * }> $rows
         */
        $rows = $tripRepo->getSummaryPerVehicle();

        return new StreamedResponse(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['vehicleId', 'model', 'total_km', 'total_fuel', 'avg_km_per_l']);

            foreach ($rows as $r) {
                $totalKm = (float) $r['total_km'];
                $totalFuel = max(1.0, (float) $r['total_fuel']);

                fputcsv($handle, [
                    $r['vehicle_id'],
                    $r['model'],
                    $totalKm,
                    $totalFuel,
                    round($totalKm / $totalFuel, 2),
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="report_summary_' . date('Ymd_His') . '.csv"',
        ]);
    }

    #[Route('/reports/summary/pdf', name: 'report_summary_pdf')]
    public function reportSummaryPdf(TripRepository $tripRepo): Response
    {
        $rows = $tripRepo->getSummaryPerVehicle();

        $html = $this->renderView('reports/summary_pdf.html.twig', [
            'rows' => $rows,
            'generatedAt' => new \DateTime(),
        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="report_summary_' . date('Ymd_His') . '.pdf"',
            ]
        );
    }
}
