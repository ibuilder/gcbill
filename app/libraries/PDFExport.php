<?php

namespace App\Libraries;

// --- Choose ONE library ---
// Option 1: Dompdf (Install: composer require dompdf/dompdf)
// use Dompdf\Dompdf;
// use Dompdf\Options;

// Option 2: TCPDF (Install: composer require tecnickcom/tcpdf)
// use TCPDF;

class PDFExport
{
    /**
     * Generates a PDF document from HTML content using the chosen library.
     *
     * @param string $htmlContent The HTML content to convert to PDF.
     * @param string $filename The desired filename for the PDF output.
     * @param string $outputMode 'I' for inline, 'D' for download, 'F' for file, 'S' for string.
     * @param string $paperSize Paper size (e.g., 'A4', 'letter').
     * @param string $orientation Paper orientation ('portrait' or 'landscape').
     * @return mixed Depending on the output mode, might return string or void.
     * @throws \Exception If the chosen PDF library is not available or fails.
     */
    public static function generateFromHtml(
        string $htmlContent,
        string $filename = 'document.pdf',
        string $outputMode = 'D',
        string $paperSize = 'letter',
        string $orientation = 'portrait'
    ) {
        // --- Option 1: Using Dompdf ---
        /* // Uncomment this block if using Dompdf
        if (!class_exists(\Dompdf\Dompdf::class)) {
            throw new \Exception("Dompdf library is not available. Please install it via Composer: composer require dompdf/dompdf");
        }
        try {
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true); // Enable carefully for external images/CSS
            // Set temporary directory if needed (check Dompdf documentation)
            // $options->set('tempDir', '/path/to/writable/tmp');
            $options->set('defaultFont', 'DejaVu Sans'); // Set a default font supporting UTF-8 if needed

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($htmlContent, 'UTF-8'); // Specify UTF-8 encoding

            // Set paper size and orientation
            $dompdf->setPaper($paperSize, $orientation);

            // Render the HTML as PDF
            $dompdf->render();

            // Output the generated PDF
            // 'I': Send the file inline to the browser.
            // 'D': Send to the browser and force a download with the name given by $filename.
            // 'F': Save to a local file with the name given by $filename. (Returns nothing)
            // 'S': Return the PDF as a string.
            if ($outputMode === 'S') {
                return $dompdf->output();
            } elseif ($outputMode === 'F') {
                 $dompdf->output(['compress' => 1]); // Output compressed PDF to file
                 file_put_contents($filename, $dompdf->output());
                 return; // Or return true/false based on success
            } else {
                // For 'I' or 'D'
                $dompdf->stream($filename, ["Attachment" => ($outputMode === 'D')]);
                exit; // Stop script execution after sending PDF for stream modes
            }
        } catch (\Exception $e) {
             error_log("Dompdf Error: " . $e->getMessage());
             throw new \Exception("Failed to generate PDF using Dompdf.", 0, $e);
        }
        */ // End Dompdf block

        // --- Option 2: Using TCPDF ---
        /* // Uncomment this block if using TCPDF
        if (!class_exists(\TCPDF::class)) {
            throw new \Exception("TCPDF library is not available. Please install it via Composer: composer require tecnickcom/tcpdf");
        }
        try {
            // Adjust orientation parameter for TCPDF ('P' or 'L')
            $tcpdfOrientation = ($orientation === 'landscape') ? 'L' : 'P';

            // Create new PDF document
            $pdf = new \TCPDF($tcpdfOrientation, PDF_UNIT, $paperSize, true, 'UTF-8', false);

            // Set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Your Application Name'); // Replace with your app name
            $pdf->SetTitle($filename);
            $pdf->SetSubject('Generated Document');

            // Set default header/footer data (optional)
            // $pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING);
            // $pdf->setFooterData(array(0,64,0), array(0,64,128));
            // $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            // $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // Set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // Set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // Set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // Set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // Set some language-dependent strings (optional)
            // if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            //     require_once(dirname(__FILE__).'/lang/eng.php');
            //     $pdf->setLanguageArray($l);
            // }

            // Set font
            $pdf->SetFont('dejavusans', '', 10); // Use a font that supports UTF-8

            // Add a page
            $pdf->AddPage();

            // Write HTML content
            $pdf->writeHTML($htmlContent, true, false, true, false, '');

            // Close and output PDF document
            // 'I': Send the file inline to the browser.
            // 'D': Send to the browser and force a download with the name given by $filename.
            // 'F': Save to a local file with the name given by $filename. (Returns nothing)
            // 'S': Return the PDF as a string.
            return $pdf->Output($filename, $outputMode);

        } catch (\Exception $e) {
            error_log("TCPDF Error: " . $e->getMessage());
            throw new \Exception("Failed to generate PDF using TCPDF.", 0, $e);
        }
        */ // End TCPDF block


        // --- Error if no library block is uncommented ---
        throw new \Exception("No PDF generation library (Dompdf or TCPDF) is configured/uncommented in PDFExport class.");
    }

    // Add other specific PDF generation methods as needed (e.g., for specific reports with headers/footers)
}