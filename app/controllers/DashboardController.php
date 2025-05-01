<?php

namespace App\Controllers;

use App\Controller; // Extends base Controller

class DashboardController extends Controller {

    public function __construct() {
        parent::__construct();
        // Ensure user is logged in (handled globally in index.php, but good practice)
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/login');
        }
    }

    /**
     * Show the main application dashboard.
     */
    public function index(): void {
        // Prepare data for the dashboard
        // Example: Get counts, recent activity, etc.
        $totalProjects = $this->db->selectValue("SELECT COUNT(*) FROM projects");
        $activeProjects = $this->db->selectValue("SELECT COUNT(*) FROM projects WHERE status = 'active'");
        $activeProjectsContractAmount = $this->db->selectValue("SELECT SUM(contract_amount) FROM projects WHERE status = 'active'");
        $draftBillings = $this->db->selectValue("SELECT COUNT(*) FROM billings WHERE status = 'draft'");
        $submittedBillings = $this->db->selectValue("SELECT COUNT(*) FROM billings WHERE status = 'submitted'");
        $approvedBillings = $this->db->selectValue("SELECT COUNT(*) FROM billings WHERE status = 'approved'");

        // Calculate total amount of current payment dues.
        $totalApprovedBillingsAmount = $this->db->selectValue(
            "SELECT COALESCE(SUM(
                (SELECT 
                  COALESCE(SUM(bd.work_completed_this_period + bd.materials_stored_this_period), 0)
                 FROM billing_details bd 
                WHERE bd.billing_id = b.id)
            ), 0) AS total_approved_amount
            FROM billings b
            WHERE b.status = 'approved'"
        );
        $totalPaidBillingsAmount = $this->db->selectValue("SELECT COALESCE(SUM(total_earned_less_retainage),0) FROM billings WHERE status = 'paid'");

        $totalCurrentPaymentDues = $totalApprovedBillingsAmount - $totalPaidBillingsAmount;
        
        $totalRetainage = $this->db->selectValue(
            "SELECT COALESCE(SUM(
                (SELECT 
                  COALESCE(SUM(bd.work_completed_this_period + bd.materials_stored_this_period), 0) * b.retainage_rate
                 FROM billing_details bd 
                WHERE bd.billing_id = b.id)
            ),0) FROM billings b"
        );

        // Set data for the view
        $viewData = [
            'pageTitle' => 'Dashboard',
            'activeNav' => 'dashboard', // For highlighting nav item
            'totalProjects' => $totalProjects ?? 0,
            'activeProjects' => $activeProjects ?? 0,
            'activeProjectsContractAmount' => $activeProjectsContractAmount ?? 0,
            'draftBillings' => $draftBillings ?? 0,
            'submittedBillings' => $submittedBillings ?? 0,
            'approvedBillings' => $approvedBillings ?? 0,
            'totalCurrentPaymentDues' => $totalCurrentPaymentDues ?? 0,
            'totalRetainage' => $totalRetainage ?? 0
            // Add more data as needed
        ];

        // Render the dashboard view
        $this->view->output('dashboard.html', $viewData);
    }
}