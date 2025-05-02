<?php

namespace App\Helpers;

use App\Libraries\PDFExport; // Use the library

class PDFHelper {

    /**
     * Generates a PDF from HTML content using the PDFExport library.
     *
     * @param string $htmlContent HTML to convert.
     * @param string $filename Desired filename.
     * @param string $outputMode 'I' (inline), 'D' (download), etc.
     * @return mixed Output depends on the library and mode.
     * @throws \Exception If library fails.
     */
    public static function createFromHtml(string $htmlContent, string $filename = 'document.pdf', string $outputMode = 'D') {
        try {
            return PDFExport::generateFromHtml($htmlContent, $filename, $outputMode);
        } catch (\Exception $e) {
            // Log the error or handle it appropriately
            error_log("PDFHelper Error: " . $e->getMessage());
            // Optionally re-throw or display a user-friendly error
            throw $e; // Re-throw for controller to handle
        }
    }

    // Add other specific PDF helper methods if needed
    // e.g., public static function generateInvoicePdf(...) { ... }
}