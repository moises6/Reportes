<?php

require './vendor/autoload.php';

use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class ReportController
{
    private PDO $conn;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function getFilters(): array
    {
        $stmt = $this->conn->prepare("SELECT DISTINCT producto, proveedor, vencimiento FROM tbl_invesproduct");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFilterValues(string $filtro): array
    {
        $query = "SELECT DISTINCT $filtro FROM tbl_invesproduct";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generateReport(string $filtro, string $valor, string $formato): string
    {
        $query = "SELECT * FROM tbl_invesproduct WHERE $filtro = :valor";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':valor', $valor);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        switch ($formato) {
            case 'pdf':
                return $this->generatePDF($data);
            case 'excel':
                return $this->generateExcel($data);
            case 'csv':
                return $this->generateCSV($data);
            default:
                throw new InvalidArgumentException('Formato no soportado');
        }
    }

    private function generatePDF(array $data): string
    {
        $mpdf = new Mpdf();
        $html = $this->buildHTMLTable($data);
        $mpdf->WriteHTML($html);
        $filename = 'reportes/reporte_' . time() . '.pdf';
        $mpdf->Output(__DIR__ . '/../reports/' . $filename, 'F');
        return $filename;
    }

    private function generateExcel(array $data): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $this->populateSpreadsheet($sheet, $data);
        $filename = 'reportes/reporte_' . time() . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save(__DIR__ . '/../public/' . $filename);
        return $filename;
    }

    private function generateCSV(array $data): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $this->populateSpreadsheet($sheet, $data);
        $filename = 'reportes/reporte_' . time() . '.csv';
        $writer = new Csv($spreadsheet);
        $writer->save(__DIR__ . '/../public/' . $filename);
        return $filename;
    }

    private function buildHTMLTable(array $data): string
    {
        $html = '<table><thead><tr>';
        foreach (array_keys($data[0]) as $header) {
            $html .= "<th>$header</th>";
        }
        $html .= '</tr></thead><tbody>';
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= "<td>$cell</td>";
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }

    private function populateSpreadsheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $data): void
    {
        $headers = array_keys($data[0]);
        $row = 1;
        foreach ($headers as $column => $header) {
            $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column + 1) . $row;
            $sheet->setCellValue($cellAddress, $header);
        }

        $row++;
        foreach ($data as $rowData) {
            $column = 0;
            foreach ($rowData as $value) {
                $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column + 1) . $row;
                $sheet->setCellValue($cellAddress, $value);
                $column++;
            }
            $row++;
        }
    }
}
