<?php

namespace App\Helpers;

use App\Libraries\ExcelExport; // Use the library

class ExcelHelper {

    /**
     * Generates an Excel file from an array using the ExcelExport library.
     *
     * @param array $data Associative array or array of arrays.
     * @param string $filename Desired filename without extension.
     * @param array|null $headers Optional headers array.
     * @return void Outputs file to browser.
     * @throws \Exception If library fails.
     */
    public static function exportArray(array $data, string $filename = 'export', ?array $headers = null): void {
        try {
            ExcelExport::generateFromArray($data, $filename, $headers);
        } catch (\Exception $e) {
            // Log the error or handle it appropriately
            error_log("ExcelHelper Error: " . $e->getMessage());
            // Optionally re-throw or display a user-friendly error
            throw $e; // Re-throw for controller to handle
        }
    }

    // Add other specific Excel helper methods if needed
    // e.g., public static function exportBillingReport(...) { ... }
}