<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat; // For formatting cells

class ExcelExport
{
    /**
     * Generates an Excel file (XLSX) from an array of data.
     *
     * @param array $data Associative array or array of arrays representing rows.
     * @param string $filename The desired filename for the Excel output (without extension).
     * @param array|null $headers Optional array of headers. If null, uses keys from the first data row.
     * @param array $columnFormats Optional array mapping header keys to PhpSpreadsheet NumberFormat constants (e.g., ['amount' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE]).
     * @return void Outputs the file directly to the browser for download.
     * @throws \Exception If PhpSpreadsheet library is not available.
     * @throws \PhpOffice\PhpSpreadsheet\Exception If spreadsheet operations fail.
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception If writing the file fails.
     */
    public static function generateFromArray(
        array $data,
        string $filename = 'export',
        ?array $headers = null,
        array $columnFormats = []
    ): void {
        if (!class_exists(Spreadsheet::class)) {
            throw new \Exception("PhpSpreadsheet library is not available. Please install it via Composer: composer require phpoffice/phpspreadsheet");
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Determine headers and header keys (for data mapping)
        $headerKeys = [];
        if ($headers === null && !empty($data)) {
            // Use keys from the first row as headers and keys
            $firstRow = reset($data);
            $headerKeys = array_keys((array)$firstRow);
            $headers = $headerKeys; // Use the keys as display headers too
        } elseif (is_array($headers) && !empty($headers)) {
             // If headers are provided, assume they are the keys unless they are numerically indexed
             // This assumes associative $headers like ['db_col' => 'Display Header'] or just ['col1', 'col2']
             if (isset($headers[0])) { // Simple array like ['Header 1', 'Header 2']
                 $headerKeys = $headers; // Assume keys match headers if not associative
             } else { // Associative array like ['db_col' => 'Display Header']
                 $headerKeys = array_keys($headers);
                 $headers = array_values($headers); // Use the values for display
             }
        } else {
            $headers = []; // No data or explicit headers
            $headerKeys = [];
        }


        // Write Headers
        $colIndex = 1; // Use column index (1-based) for easier formatting
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($colIndex, 1, $header);
            $colIndex++;
        }

        // Write Data
        $rowNum = 2; // Start data from row 2
        foreach ($data as $row) {
            $colIndex = 1;
            $rowData = (array) $row; // Ensure row is treated as an array
            foreach ($headerKeys as $headerKey) { // Iterate using header keys to map data correctly
                $cellValue = $rowData[$headerKey] ?? ''; // Use null coalescing for missing keys
                $sheet->setCellValueByColumnAndRow($colIndex, $rowNum, $cellValue);

                // Apply formatting if specified for this column key
                if (isset($columnFormats[$headerKey])) {
                    $sheet->getStyleByColumnAndRow($colIndex, $rowNum)
                          ->getNumberFormat()
                          ->setFormatCode($columnFormats[$headerKey]);
                } elseif (is_numeric($cellValue) && strpos($headerKey, 'amount') !== false || strpos($headerKey, 'cost') !== false || strpos($headerKey, 'price') !== false) {
                     // Basic auto-detection for currency/accounting format based on key name
                     $sheet->getStyleByColumnAndRow($colIndex, $rowNum)
                           ->getNumberFormat()
                           ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1); // Or FORMAT_CURRENCY_USD_SIMPLE etc.
                }

                $colIndex++;
            }
            $rowNum++;
        }

        // Auto-size columns (optional, can be slow for large datasets)
        $colIndex = 1;
        foreach ($headers as $header) {
            // Convert column index to letter for setAutoSize
             $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            $colIndex++;
        }


        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving over HTTPS, might need these headers
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        try {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
             error_log("Excel Export Error (Writer): " . $e->getMessage());
             // Avoid sending headers if saving fails before output starts
             // Consider sending an error message instead
             echo "Error generating Excel file."; // Simple error message
        }
        exit; // Stop script execution after sending file or error
    }

    // Add other specific Excel generation methods as needed
    // e.g., public static function generateBillingReportExcel(...) { ... }
}