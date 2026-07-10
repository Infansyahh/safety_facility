<?php

/**
 * Helper export Excel (.xlsx) pake PhpSpreadsheet.
 * Header biru + putih bold, semua cell dikasih border, kolom auto-width.
 * Taruh file ini satu folder sama vendor/ (root project), lalu di tiap
 * halaman data_inspeksi_*.php cukup:
 *   require '../vendor/autoload.php';
 *   require '../export_excel_helper.php';
 *   export_excel_xlsx($headers, $data, 'Nama_File');
 *   exit();
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

function export_excel_xlsx(array $headers, array $data, string $filename, string $sheetTitle = 'Laporan'): void
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($sheetTitle);

    $lastCol = Coordinate::stringFromColumnIndex(count($headers));

    // Header
    $sheet->fromArray($headers, null, 'A1');
    $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType'   => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '2F5597'], // biru
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical'   => Alignment::VERTICAL_CENTER,
        ],
    ]);
    $sheet->getRowDimension(1)->setRowHeight(22);

    // Data
    $row = 2;
    foreach ($data as $r) {
        $sheet->fromArray(array_values($r), null, "A{$row}");
        $row++;
    }
    $lastRow = max($row - 1, 1);

    // Border semua cell (header + data)
    $sheet->getStyle("A1:{$lastCol}{$lastRow}")->getBorders()
        ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Auto width tiap kolom
    foreach (range('A', $lastCol) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Freeze header row biar enak scroll
    $sheet->freezePane('A2');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
}
