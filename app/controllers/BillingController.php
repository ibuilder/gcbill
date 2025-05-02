<?php

namespace App\Controllers; // This is now correct

use TCPDF;

use App\Database;
use App\Libraries\Auth;
use App\Models\Billing;
use App\Models\BillingDetail;
use App\Models\Project;
use App\Models\SOV;
use App\Helpers\SecurityHelper;
use App\Helpers\CalculationHelper; // We'll create this helper later
use App\Helpers\ViewHelper;


class BillingController extends BaseController { // Changed to BillingController
    private Billing $billingModel;   
    private BillingDetail $billingDetailModel;    
    private Project $projectModel;
    private SOV $sovModel;

    
    public function __construct(Database $db) {
        parent::__construct($db);
        

        $this->billingModel = new Billing();    
        $this->billingDetailModel = new BillingDetail();

        $this->projectModel = new Project($this->db);
        $this->sovModel = new Sov($this->db);
    }

    /**
     * List all billings for a specific project.
     * Accessed via /projects/{projectId}/billings
     */
    public function index(int $projectId) {
        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            $_SESSION['flash_error'] = 'Project not found.';
            header('Location: /projects');
            exit;
        }

        $billings = $this->billingModel->findByProjectId($projectId); 
        $nextBillingNumber = $this->billingModel->getNextBillingNumber($projectId);

        $this->view->output('billings/list.html', [
            'pageTitle' => 'Billings for ' . htmlspecialchars($project['project_name']),
            'activeNav' => 'projects', // Keep projects nav active
            'project' => $project,
            'billings' => $billings,
            'nextBillingNumber' => $nextBillingNumber
        ]);
    }

    /**
     * Show the form to create a new billing period header.
     * Accessed via /projects/{projectId}/billings/create
     */
    public function create(int $projectId) {
        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            $_SESSION['flash_error'] = 'Project not found.';
            header('Location: /projects');
            exit;
        }

        $nextBillingNumber = $this->billingModel->getNextBillingNumber($projectId);
        $latestBilling = $this->billingModel->findLatestCompletedByProjectId($projectId); // Get previous end date

        // Pre-fill dates if possible
        $today = date('Y-m-d');
        $defaultStartDate = $latestBilling ? date('Y-m-d', strtotime($latestBilling['period_end_date'] . ' +1 day')) : $project['start_date'];

        $formData = $_SESSION['form_data'] ?? [
            'billing_number' => $nextBillingNumber,
            'period_start_date' => $defaultStartDate,
            'period_end_date' => $today,
            'billing_date' => $today,
            'status' => 'draft',
            'retainage_rate' => $project['retainage_percentage'] // Default to project retainage
        ];
        unset($_SESSION['form_data']);

        $this->view->output('billings/create.html', [
            'pageTitle' => 'Create New Billing for ' . htmlspecialchars($project['project_name']),
            'activeNav' => 'projects',
            'project' => $project,
            'billing' => $formData, // Use 'billing' key for consistency with form partial
            'formAction' => '/projects/' . $projectId . '/billings/store'
        ]);
    }

    /**
     * Store a new billing period header.
     * Accessed via POST /projects/{projectId}/billings/store
     */
    public function store(int $projectId) {
        if (!SecurityHelper::validateToken($_POST[SecurityHelper::getFormInputName()] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid request token.';
            header('Location: /projects/' . $projectId . '/billings/create');
            exit;
        }

        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            $_SESSION['flash_error'] = 'Project not found.';
             header('Location: /projects');
             exit;
        }

        $data = $_POST;
        $data['project_id'] = $projectId;

        // Basic Validation
        if (empty($data['billing_number']) || empty($data['period_end_date']) || empty($data['billing_date'])) {
            $_SESSION['flash_error'] = 'Billing Number, Period End Date, and Billing Date are required.';
            $_SESSION['form_data'] = $data;
            $this->redirect('/projects/' . $projectId . '/billings/create'); return;
        }
        if ($this->billingModel->billingNumberExists($projectId, (int)$data['billing_number'])) {
             $_SESSION['flash_error'] = 'Billing Number already exists for this project.';
             $_SESSION['form_data'] = $data;
             $this->redirect('/projects/' . $projectId . '/billings/create'); return;
        }
        // Add date validation (end date >= start date, etc.) if needed

        $newBillingId = $this->billingModel->create($data);

        if ($newBillingId) {
            $_SESSION['flash_success'] = 'Billing period created successfully. Now enter the details.';
            unset($_SESSION['form_data']);
            // Redirect to the edit page for the newly created billing
            header('Location: /billings/edit/' . $newBillingId);
            exit;
        } else {
            $_SESSION['flash_error'] = 'Failed to create billing period.';
            $_SESSION['form_data'] = $data;
            $this->redirect('/projects/' . $projectId . '/billings/create');
        }
    }

    /**
     * Show the main billing edit form (AIA G702/G703 style).
     * Accessed via /billings/edit/{billingId}
     */
    public function edit(int $billingId) {
        
        if (!SecurityHelper::validateToken($_POST[SecurityHelper::getFormInputName()] ?? null) && isset($_POST[SecurityHelper::getFormInputName()])) {
            $_SESSION['flash_error'] = 'Invalid request token.';
             header('Location: /billings/edit/' . $billingId);
             exit;
         }


        $billing = $this->billingModel->findById($billingId);
        if (!$billing) {
            $_SESSION['flash_error'] = 'Billing not found.';
            header('Location: /projects');
            exit;// Or redirect to a relevant project page if possible
            
        }

        $project = $this->projectModel->findById($billing['project_id']);
        if (!$project) {
             $_SESSION['flash_error'] = 'Associated project not found.';
             $this->redirect('/projects');
             return;
        }

         // Check billing status
        if ($billing['status'] !== 'draft') {
            $_SESSION['flash_error'] = 'You can only edit draft billings.';
            header('Location: /billings/view/' . $billingId); // Redirect to billing view
        }

        // We will load SOV and billing details via AJAX in the view
        // But pass the main billing header info
        $this->view->output('billings/edit.html', [
            'pageTitle' => 'Edit Billing #' . $billing['billing_number'] . ' for ' . htmlspecialchars($project['project_name']),
            'activeNav' => 'projects',
            'project' => $project,
            'billing' => $billing,
            // Pass IDs needed for AJAX calls
            'billingId' => $billingId,
            'projectId' => $project['id']
        ]);
    }

    /**
     * AJAX endpoint to fetch data needed for the billing edit form.
     * Includes calculation of previous payments.
     * Accessed via GET /billings/{billingId}/data
     */
    public function getBillingData(int $billingId) {
        $billing = $this->billingModel->findById($billingId);
        if (!$billing) {
            $this->jsonResponse(['success' => false, 'message' => 'Billing not found.'], 404);
             exit;
        }
        

        $projectId = $billing['project_id'];
        $billingNumber = $billing['billing_number'];
        $project = $this->projectModel->findById($projectId); // Fetch project data
       if (!$project) {
             $this->jsonResponse(['success' => false, 'message' => 'Associated project not found.'], 404); return;
        }

        // --- Calculate Previous Payments ---
        $previousPaymentsTotal = 0.0;
        if ($billingNumber > 1) {
            // Find the latest approved/paid billing *before* this one
            $query = "SELECT * FROM billings
                      WHERE project_id = ? AND billing_number < ? AND status IN ('approved', 'paid')
                      ORDER BY billing_number DESC LIMIT 1";
            $previousBilling = $this->db->selectOne($query, [$projectId, $billingNumber]);

            if ($previousBilling) {
                // Need to recalculate the summary for the previous billing to get its 'total_earned_less_retainage'
                $previousBillingDetails = $this->calculateBillingDetailsForSummary($previousBilling['id'], $project);
                $previousSummary = CalculationHelper::calculateBillingSummary(
                    $previousBilling,
                    $project,
                    $previousBillingDetails,
                    0.0 // Previous payments for the *previous* billing would be needed for full accuracy, but 0 is often sufficient here
                );
                $previousPaymentsTotal = $previousSummary['total_earned_less_retainage'];
            }
        }

        // --- Calculate Current Billing Details ---
        $billingData = $this->calculateBillingDetailsForSummary($billingId, $project);

        $this->jsonResponse([
            'success' => true,
            'billingHeader' => $billing, // Use the initially fetched billing header
            'project' => $project,
            'billingDetails' => $billingData,
            'previousPaymentsTotal' => $previousPaymentsTotal, // Send calculated previous payments
        ]);
    }

    /**
     * Helper function to calculate the detailed lines needed for summary calculation.
     * Used by getBillingData and potentially the view method.
     */
    private function calculateBillingDetailsForSummary(int $billingId, array $project): array {
        $billing = $this->billingModel->findById($billingId); // Need header for rates and number
        if (!$billing) return [];

        $projectId = $billing['project_id'];
        $billingNumber = $billing['billing_number'];

        $sovItems = $this->sovModel->findByProjectId($projectId);
        $currentDetailsRaw = $this->billingDetailModel->findByBillingId($billingId);
        $currentDetailsMap = [];
        foreach ($currentDetailsRaw as $detail) {
            $currentDetailsMap[$detail['sov_item_id']] = $detail;
        }

        // Determine retainage rates for calculation
        $retainageRate = isset($billing['retainage_rate']) && $billing['retainage_rate'] !== null
            ? (float)$billing['retainage_rate']
            : (float)($project['retainage_percentage'] ?? 0.0);
        $retainageWorkPercent = $retainageRate; // Assuming same rate for now
        $retainageStoredPercent = $retainageRate; // Assuming same rate for now

        $calculatedDetails = [];
        foreach ($sovItems as $sov) {
            $sovId = $sov['id'];
            // Get previous totals (using approved/paid statuses by default)
            $previousTotals = $this->billingDetailModel->getPreviousTotalsForSovItem($projectId, $sovId, $billingNumber);
            $currentDetailInput = $currentDetailsMap[$sovId] ?? []; // Get saved values for 'this period'

            $calculatedLine = CalculationHelper::calculateDetailLine(
                $sov,
                $currentDetailInput,
                $previousTotals,
                $retainageWorkPercent,
                $retainageStoredPercent
            );
            $calculatedDetails[] = $calculatedLine;
        }
        return $calculatedDetails;
    }

    /**
     * Update billing details (bulk save).
     * Accessed via POST /billings/update/{billingId}
     */
    public function update(int $billingId) {
         if (!SecurityHelper::validateToken($_POST[SecurityHelper::getFormInputName()] ?? null)) {
             $this->jsonResponse(['success' => false, 'message' => 'Invalid request token.'], 403);
              exit;
         }

         $billing = $this->billingModel->findById($billingId);

         if (!$billing) {
             $this->jsonResponse(['success' => false, 'message' => 'Billing not found.'], 404); return;
         }
         // Add check: Only allow editing 'draft' billings?
         // if ($billing['status'] !== 'draft') {
         //     $this->jsonResponse(['success' => false, 'message' => 'Cannot update billing details, status is not draft.'], 403); return;
         // }

         $detailsData = $_POST['details'] ?? []; // Expect details in format: details[sov_id][field_name]
         if (empty($detailsData)) {
              $this->jsonResponse(['success' => false, 'message' => 'No billing details provided.'], 400); return;
         }

         if ($this->billingDetailModel->saveBulk($billingId, $detailsData)) {
             // Optionally update billing header status or updated_at timestamp here
             $this->billingModel->update($billingId, ['updated_at' => date('Y-m-d H:i:s')]); // Touch updated_at
             $this->jsonResponse(['success' => true, 'message' => 'Billing details saved successfully.']);
         } else {
             $this->jsonResponse(['success' => false, 'message' => 'Failed to save billing details.'], 500);
         }
    }

    /**
     * Update billing header info only.
     * Accessed via POST /billings/update-header/{billingId}
     */
    public function updateHeader(int $billingId): void {
         if (!SecurityHelper::validateToken($_POST[SecurityHelper::getFormInputName()] ?? null)) {
             $_SESSION['flash_error'] = 'Invalid request token.';
             $this->redirect('/billings/edit/' . $billingId); return;
         }

         $billing = $this->billingModel->findById($billingId);
         if (!$billing) {
             $_SESSION['flash_error'] = 'Billing not found.';
             $this->redirect('/projects'); return;
         }

         $data = $_POST;
         // Basic Validation
         if (empty($data['period_end_date']) || empty($data['billing_date'])) {
             $_SESSION['flash_error'] = 'Period End Date and Billing Date are required.';
             $_SESSION['form_data'] = $data; // Keep data for form refill
             $this->redirect('/billings/edit/' . $billingId); return;
         }
         // Add more validation as needed (status changes, dates)

         if ($this->billingModel->update($billingId, $data) >= 0) {
             $_SESSION['flash_success'] = 'Billing header updated successfully.';
             unset($_SESSION['form_data']);
             $this->redirect('/billings/edit/' . $billingId);
         } else {
             $_SESSION['flash_error'] = 'Failed to update billing header.';
             $_SESSION['form_data'] = $data;
             $this->redirect('/billings/edit/' . $billingId);
         }
    }

    /**
     * Delete a billing period (likely only drafts).   
     * Accessed via POST /billings/delete/{billingId}
     */
    public function delete(int $billingId) {
         $billing = $this->billingModel->findById($billingId);
          if (!$billing) {
              $_SESSION['flash_error'] = 'Billing not found.';
               header('Location: /projects');
               exit;

          }
        
          // Check billing status
          if ($billing['status'] !== 'draft') {
              $_SESSION['flash_error'] = 'You can only delete draft billings.';     
               $this->redirect('/projects/' . $billing['project_id'] . '/billings'); // Redirect to project's billing list
          }
        
          $projectId = $billing['project_id']; // Get project ID before deleting
          if (!SecurityHelper::validateToken($_POST[SecurityHelper::getFormInputName()] ?? null) && isset($_POST[SecurityHelper::getFormInputName()])) {
              $_SESSION['flash_error'] = 'Invalid request token.';
              header('Location: /projects/' . $projectId . '/billings');
              exit;
          }

         if ($this->billingModel->delete($billingId) > 0) {
             $_SESSION['flash_success'] = 'Billing deleted successfully.';
         } else {
             $_SESSION['flash_error'] = 'Failed to delete billing.';
         }
         $this->redirect('/projects/' . $projectId . '/billings');
     }
     /**
     * Display a non-editable view of the billing application.
     */
    public function submit(int $billingId): void
    {
        $billing = $this->billingModel->findById($billingId);
        if (!$billing) {
            $_SESSION['flash_error'] = 'Billing not found.';
            $this->redirect('/projects');
            return;
        }
        $this->billingModel->update($billingId, ['status' => 'submitted']);
        $_SESSION['flash_success'] = 'Billing status updated to submitted.';
        $this->redirect('/billings/view/' . $billingId);
    }

    /**
     * Approve a billing.
     */
    public function approve(int $billingId): void
    {
        $billing = $this->billingModel->findById($billingId);
        if (!$billing) {
            $_SESSION['flash_error'] = 'Billing not found.';
            $this->redirect('/projects');
            return;
        }
        $this->billingModel->update($billingId, ['status' => 'approved']);
        $_SESSION['flash_success'] = 'Billing status updated to approved.';
        $this->redirect('/billings/view/' . $billingId);
    }

    /**
     * Mark a billing as paid.
     */
    public function pay(int $billingId): void
    {
        $billing = $this->billingModel->findById($billingId);
        if (!$billing) {
            $_SESSION['flash_error'] = 'Billing not found.';
            $this->redirect('/projects');
            return;
        }
        $this->billingModel->update($billingId, ['status' => 'paid']);
        $_SESSION['flash_success'] = 'Billing status updated to paid.';
        $this->redirect('/billings/view/' . $billingId);
    }
    /**
     * Display a non-editable view of the billing application.
     * Accessed via /billings/view/{billingId}
     */
    public function view(int $billingId): void {
        $billing = $this->billingModel->findById($billingId);
        if (!$billing) {
            $_SESSION['flash_error'] = 'Billing not found.';
            $this->redirect('/projects'); return;
        }

        $project = $this->projectModel->findById($billing['project_id']);
        if (!$project) {
             $_SESSION['flash_error'] = 'Associated project not found.';
             $this->redirect('/projects'); return;
        }

        // Calculate the details needed for the view
        $calculatedDetails = $this->calculateBillingDetailsForSummary($billingId, $project);

        // Calculate previous payments for the summary view
        $previousPaymentsTotal = 0.0;
        if ($billing['billing_number'] > 1) {
            $query = "SELECT * FROM billings
                      WHERE project_id = ? AND billing_number < ? AND status IN ('approved', 'paid')
                      ORDER BY billing_number DESC LIMIT 1";
            $previousBilling = $this->db->selectOne($query, [$project['id'], $billing['billing_number']]);
            if ($previousBilling) {
                $previousBillingDetails = $this->calculateBillingDetailsForSummary($previousBilling['id'], $project);
                $previousSummary = CalculationHelper::calculateBillingSummary($previousBilling, $project, $previousBillingDetails, 0.0);
                $previousPaymentsTotal = $previousSummary['total_earned_less_retainage'];
            }
        }

        // Calculate the final summary for display
        $summary = CalculationHelper::calculateBillingSummary(
            $billing,
            $project,
            $calculatedDetails,
            $previousPaymentsTotal
        );

        // Check if we should generate PDF
        if (isset($_GET['pdf'])) {
            require_once __DIR__ . '/../../vendor/autoload.php';
            // create new PDF document
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Construction Billing App');
            $pdf->SetTitle('Billing #' . $billing['billing_number']);
            $pdf->SetSubject('Billing Application');

            // add a page
            $pdf->AddPage();
            $currentDate = date('Y-m-d H:i:s');
            // Generate HTML content
            $html = '<h1>Billing #' . $billing['billing_number'] . '</h1>';
            $html .= '<p>Generated on: '.$currentDate.'</p>';
            // Output billing details
            $html .= '<h2>Project: ' . htmlspecialchars($project['project_name']) . '</h2>';
            // Output other billing data in HTML format...
            $html .='<p>Total Earned Less Retainage: ' . htmlspecialchars($summary['total_earned_less_retainage']).'</p>';


            // output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

            // reset pointer to the last page
            $pdf->lastPage();
            $pdfFileName = 'billing-' . $billingId . '.pdf';
            //Close and output PDF document
            $pdf->Output($pdfFileName, 'D');
        } else {
            $this->view->output('billings/view.html', [
                'pageTitle' => 'View Billing #' . $billing['billing_number'] . ' - ' . htmlspecialchars($project['project_name']),
                'activeNav' => 'projects',
                'project' => $project,
                'billing' => $billing,
                'billingDetails' => $calculatedDetails, // Pass calculated details
                'summary' => $summary, // Pass calculated summary
                'viewHelper' => new ViewHelper() // Pass instance for use in template
            ]);
        }
    }

    /** Helper for JSON responses */
    private function jsonResponse(array $data, int $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        // Important: exit after sending JSON response for AJAX calls
        exit;
    }
}