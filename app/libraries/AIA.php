<?php

namespace App\Libraries;

use App\Models\Project;
use App\Models\SOV; // Schedule of Values model
use App\Models\ApplicationForPayment; // Example: You NEED a model for payment applications
use App\Models\ApplicationForPaymentDetail; // Example: You NEED a model for the details of each application line item
use App\Models\ChangeOrder; // Example: You NEED a model for change orders
use App\Database;
use App\Helpers\CalculationHelper; // Assuming you have this helper for calculations

class AIA
{
    protected Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Generates data needed for an AIA G702/G703 style report for a specific payment application.
     *
     * @param int $applicationId The ID of the specific ApplicationForPayment record.
     * @return array An array containing structured data for the G702/G703 forms.
     * @throws \Exception If required data is missing or calculations fail.
     */
    public function generateG702G703Data(int $applicationId): array
    {
        // 1. Fetch the specific Application for Payment
        // Adjust model name and find method as needed
        $application = ApplicationForPayment::find($applicationId, $this->db);
        if (!$application) {
           throw new \Exception("Application for Payment ID {$applicationId} not found.");
        }
        $projectId = $application->project_id; // Assuming project_id is on the application record

        // 2. Fetch Project Details
        $project = Project::find($projectId, $this->db);
        if (!$project) {
            throw new \Exception("Project not found: ID {$projectId} associated with Application ID {$applicationId}");
        }

        // 3. Fetch Schedule of Values (SOV) lines for the project
        // Ensure SOV lines are ordered correctly (e.g., by line number)
        $sovLines = SOV::where('project_id', $projectId, $this->db)->orderBy('line_number', 'ASC')->get();
        if (empty($sovLines)) {
            // Decide if this is an error or just means an empty G703
            // throw new \Exception("Schedule of Values not found for Project ID {$projectId}");
        }

        // 4. Fetch Details for the CURRENT Application
        // These details contain 'work_completed_this_period', 'materials_stored_this_period' for each SOV line
        $currentAppDetails = ApplicationForPaymentDetail::where('application_id', $applicationId, $this->db)
                                ->get(['sov_id', 'work_completed_this_period', 'materials_stored_this_period']); // Fetch as array keyed by sov_id
        $currentDetailsMap = array_column($currentAppDetails, null, 'sov_id'); // Map details by SOV ID for easy lookup


        // 5. Fetch and Summarize Previous Applications' data
        $previousApplicationsSummary = $this->getPreviousApplicationsSummary($projectId, $application->application_number); // Pass app number

        // 6. Fetch and Summarize Approved Change Orders up to the end date of this application period
        $changeOrders = ChangeOrder::getApprovedChangeOrdersThroughDate($projectId, $application->period_to_date, $this->db);
        $contractChangeOrderSummary = $this->calculateChangeOrderSummary($changeOrders);

        // 7. Perform G703 Line Calculations
        $g703_lines = [];
        $retainageWorkRate = (float)($application->retainage_work_rate ?? $project->retainage_percentage ?? 0.0) / 100.0;
        $retainageStoredRate = (float)($application->retainage_stored_rate ?? $retainageWorkRate * 100.0) / 100.0; // Default stored rate to work rate if not specified on app

        foreach ($sovLines as $sovLine) {
            $sovId = $sovLine->id;
            $currentDetailData = $currentDetailsMap[$sovId] ?? ['work_completed_this_period' => 0.0, 'materials_stored_this_period' => 0.0];
            $previousLineTotals = $previousApplicationsSummary['line_item_totals'][$sovId] ?? ['work_completed' => 0.0, 'materials_stored' => 0.0];

            // Use CalculationHelper for consistency
            $lineData = CalculationHelper::calculateDetailLine(
                (array)$sovLine, // Pass SOV line as array
                (array)$currentDetailData, // Pass current detail input as array
                $previousLineTotals, // Pass previous totals for this line
                $retainageWorkRate,
                $retainageStoredRate
            );
            $g703_lines[] = $lineData;
        }

        // 8. Calculate G702 Summary Data using CalculationHelper
        // Pass application data as array, project as array, calculated G703 lines, previous summary total, CO total
        $g702_summary = CalculationHelper::calculateBillingSummary(
             (array)$application,
             (array)$project,
             $g703_lines,
             $previousApplicationsSummary['total_earned_less_retainage'], // Pass the grand total from previous apps
             $contractChangeOrderSummary['net_change']
        );


        // --- Structure the final return array ---
        return [
            'g702_summary' => $g702_summary, // Data for the G702 summary page
            'g703_lines' => $g703_lines,   // Array of lines for the G703 continuation sheet
            'project_info' => [ // Extract relevant project info
                'name' => $project->name ?? 'N/A',
                'number' => $project->project_number ?? 'N/A',
                'owner' => $project->owner_name ?? 'N/A', // Add relevant fields
                'architect' => $project->architect_name ?? 'N/A', // Add relevant fields
                // ... other project fields needed for the form
            ],
            'contract_info' => [ // Extract relevant contract info
                'date' => $project->contract_date ?? 'N/A',
                'original_sum' => $project->contract_amount ?? 0.0,
                // ... other contract fields needed for the form
            ],
            'application_info' => [ // Extract relevant application info
                 'number' => $application->application_number ?? 'N/A',
                 'period_to' => $application->period_to_date ?? 'N/A',
                 'date' => $application->application_date ?? 'N/A',
                 // ... other application fields needed for the form
            ]
        ];
    }

    /**
     * Fetches and summarizes data from previous approved applications for a project.
     *
     * @param int $projectId
     * @param int $currentApplicationNumber The number of the *current* application (to fetch ones before it).
     * @return array Summary containing 'total_earned_less_retainage' and 'line_item_totals'.
     */
    protected function getPreviousApplicationsSummary(int $projectId, int $currentApplicationNumber): array
    {
        $summary = [
            'total_earned_less_retainage' => 0.0,
            'line_item_totals' => [], // Keyed by SOV ID: ['work_completed' => X, 'materials_stored' => Y]
        ];

        // Fetch all *approved* applications for this project with number < current number
        // Adjust model and status field name as needed
        $previousApps = ApplicationForPayment::where('project_id', $projectId, $this->db)
            ->where('application_number', '<', $currentApplicationNumber)
            ->where('status', '=', 'Approved') // IMPORTANT: Only sum approved apps
            ->orderBy('application_number', 'DESC') // Get the latest approved one first
            ->get();

        if (empty($previousApps)) {
            return $summary; // No previous approved apps
        }

        // Typically, you only need the totals from the *immediately preceding* approved application.
        $latestApprovedApp = $previousApps[0]; // Get the highest numbered previous approved app

        // Fetch the details for that latest approved application
        $latestAppDetails = ApplicationForPaymentDetail::where('application_id', $latestApprovedApp->id, $this->db)
                                ->get(['sov_id', 'total_work_completed', 'materials_presently_stored']); // Fetch the calculated TO-DATE values

        foreach ($latestAppDetails as $detail) {
             $summary['line_item_totals'][$detail->sov_id] = [
                 'work_completed' => (float)($detail->total_work_completed ?? 0.0),
                 'materials_stored' => (float)($detail->materials_presently_stored ?? 0.0) // This is Col E from previous G703
             ];
        }

        // The 'total_earned_less_retainage' from the previous app is needed for G702 Line 7
        // This value should ideally be stored directly on the ApplicationForPayment record when it's approved.
        $summary['total_earned_less_retainage'] = (float)($latestApprovedApp->total_earned_less_retainage ?? 0.0);


        return $summary;
    }

     /**
     * Calculates the net impact of approved change orders.
     *
     * @param array $changeOrders Array of ChangeOrder objects/arrays.
     * @return array Summary containing 'net_change'.
     */
    protected function calculateChangeOrderSummary(array $changeOrders): array
    {
        $netChangeByChangeOrders = 0.0;
        foreach ($changeOrders as $co) {
            // Ensure amount property exists and is numeric
            $amount = is_object($co) ? ($co->amount ?? 0.0) : ($co['amount'] ?? 0.0);
            $netChangeByChangeOrders += (float)$amount;
        }
        return ['net_change' => round($netChangeByChangeOrders, 2)];
    }


    /**
     * Exports the generated G702/G703 data to a PDF using the PDFExport library.
     *
     * @param array $aiaData Data generated by generateG702G703Data.
     * @param string $filename Desired output filename.
     * @return void Outputs PDF to browser or handles as per PDFExport configuration.
     * @throws \Exception If PDF generation fails.
     */
    public function exportG702G703ToPdf(array $aiaData, string $filename = 'AIA_G702_G703.pdf'): void
    {
        // 1. Create HTML content from $aiaData using a template engine or helper
        $htmlContent = $this->renderAiaHtml($aiaData); // Implement this method

        // 2. Use the PDFExport library
        // Consider passing paper size/orientation if needed
        PDFExport::generateFromHtml($htmlContent, $filename, 'D', 'letter', 'portrait'); // 'D' for download
    }

     /**
     * Renders AIA data into an HTML string suitable for PDF generation.
     * This should load and populate a dedicated HTML template file.
     *
     * @param array $aiaData The structured data from generateG702G703Data.
     * @return string HTML content.
     */
    protected function renderAiaHtml(array $aiaData): string
    {
        // --- Best Practice: Use a Template Engine (like Twig) ---
        /* // Example using Twig (requires setup)
        try {
            // Assuming $twig is your configured Twig environment instance
            global $twig; // Or inject it into the class constructor
            if (!$twig) throw new \Exception("Twig environment not available.");
            return $twig->render('pdf/aia_g702_g703.html.twig', ['data' => $aiaData]);
        } catch (\Exception $e) {
             error_log("Error rendering AIA HTML template: " . $e->getMessage());
             return "<html><body>Error generating AIA document preview. Please check logs.</body></html>";
        }
        */

        // --- Basic Alternative: PHP Template with Output Buffering ---
        $templatePath = __DIR__ . '/../../templates/pdf/aia_g702_g703_template.php'; // Adjust path as needed

        if (!file_exists($templatePath)) {
            error_log("AIA HTML template not found at: " . $templatePath);
            return "<html><body>Error: AIA template file missing.</body></html>";
        }

        // Make data available to the template file
        extract($aiaData); // Extracts keys like $g702_summary, $g703_lines etc. into local scope

        ob_start();
        try {
            include $templatePath; // Include the template file
        } catch (\Throwable $e) { // Catch potential errors within the template
            ob_end_clean(); // Discard any partial output
            error_log("Error including AIA HTML template: " . $e->getMessage());
            return "<html><body>Error generating AIA document preview. Please check logs.</body></html>";
        }
        return ob_get_clean(); // Return the buffered content
    }

    // Add other AIA related methods as needed
}