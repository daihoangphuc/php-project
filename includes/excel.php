<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function exportToExcel($data, $headers, $filename) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Thêm headers
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $sheet->getColumnDimension($col)->setAutoSize(true);
        $col++;
    }
    
    // Thêm dữ liệu
    $row = 2;
    foreach ($data as $item) {
        $col = 'A';
        foreach ($item as $value) {
            $sheet->setCellValue($col . $row, $value);
            $col++;
        }
        $row++;
    }
    
    // Tạo file Excel
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
} 