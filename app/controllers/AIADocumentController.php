<?php

namespace App\Controllers;

use App\Models\ApplicationForPayment;
use App\Models\Project;
use App\Libraries\AIA;
use App\Libraries\Auth;
use App\Libraries\PDFExport; // Or use a PDFHelper if you created one
use App\Helpers\ViewHelper; // Assuming you have a ViewHelper
use App\Database; // Assuming BaseController doesn't automatically provide $db

class AIADocumentController extends BaseController
{
    protected Database $db;
    protected AIA $aiaGenerator;

    public function __construct()
    {
        parent::__construct(); // Call parent constructor if it exists
        $this->db = new Database(); // Instantiate Database connection
        $this->aiaGenerator = new AIA($this->db); // Instantiate AIA library

        // Authentication Check: Redirect if not logged in
        if (!Auth::isLoggedIn()) {
            // Assuming a redirect helper or function exists
            redirect('/login');
            exit;
        }
    }

    /**
     * Display a list of projects or applications to choose from for generation.
     * Or directly show a specific application's generation option if ID is provided.
     */
    public function index(?int $projectId = null)
    {
        // Permission Check (Example: only 'admin' or 'project_manager' can access)
        $user = Auth::getUser();
        if (!Auth::checkPermission($user, 'AIADocument', 'index', $this->db)) {
             // Or show an access denied view
            ViewHelper::render('errors/403');
            exit;
        }

        // Fetch data needed for the view (e.g., list of projects or applications)
        $applications = ApplicationForPayment::query($this->db)
                            ->orderBy('project_id', 'ASC')
                            ->orderBy('application_number', 'DESC')
                            ->get(); // Fetch all for selection, adjust query as needed

        // Load a view to select the application
        ViewHelper::render('aia/select_application', [
            'pageTitle' => 'Generate AIA Document',
            'applications' => $applications,
            'user' => $user // Pass user if needed in the view
        ]);
    }

    /**
     * Generate and output the AIA G702/G703 PDF for a specific application.
     *
     * @param int $applicationId
     */
    public function generatePdf(int $applicationId)
    {
         // Permission Check
        $user = Auth::getUser();
        if (!Auth::checkPermission($user, 'AIADocument', 'generatePdf', $this->db)) {
             // Or show an access denied view
             http_response_code(403);
             echo "Access Denied."; // Simple response, ideally render a view
             exit;
        }

        try {
            // 1. Generate the data using the AIA library
            $aiaData = $this->aiaGenerator->generateG702G703Data($applicationId);

            // 2. Generate the PDF using the AIA library's export method
            // This method internally calls PDFExport and renders HTML
            $filename = "AIA_G702_G703_App_{$applicationId}.pdf";
            $this->aiaGenerator->exportG702G703ToPdf($aiaData, $filename);

            // Note: exportG702G703ToPdf likely calls exit() after streaming the PDF,
            // so code below this might not execute if output mode is 'I' or 'D'.

        } catch (\Exception $e) {
            // Log the error
            error_log("AIA PDF Generation Error for App ID {$applicationId}: " . $e->getMessage());
            // Show an error view or redirect with an error message
            // Example: Redirect back with a session flash message
            $_SESSION['error_message'] = "Error generating AIA document: " . $e->getMessage();
            // Assuming a redirect helper or function exists
            redirect('/aia'); // Redirect to the selection page
            exit;
        }
    }

     /**
     * Preview the AIA G702/G703 data in an HTML format (optional).
     *
     * @param int $applicationId
     */
    public function previewHtml(int $applicationId)
    {
        // Permission Check
        $user = Auth::getUser();
        if (!Auth::checkPermission($user, 'AIADocument', 'previewHtml', $this->db)) {
             ViewHelper::render('errors/403');
             exit;
        }

        try {
            // 1. Generate the data
            $aiaData = $this->aiaGenerator->generateG702G703Data($applicationId);

            // 2. Render an HTML view using the data
            // This assumes you have a specific HTML template for previewing
            // It might reuse the same template used by renderAiaHtml in the AIA library
            ViewHelper::render('aia/preview_g702_g703', [
                'pageTitle' => "Preview AIA App #{$applicationId}",
                'data' => $aiaData, // Pass the structured data to the view
                'user' => $user
            ]);

        } catch (\Exception $e) {
            error_log("AIA HTML Preview Error for App ID {$applicationId}: " . $e->getMessage());
            $_SESSION['error_message'] = "Error generating AIA preview: " . $e->getMessage();
            redirect('/aia');
            exit;
        }
    }

    // Add other methods as needed (e.g., settings related to AIA generation)
}