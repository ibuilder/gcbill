<?php

namespace App\Controllers; // Correct namespace

use App\Database;
// Add models if more complex data is needed: Project, Billing, etc.

class DashboardController extends BaseController
{
    protected string $controllerName = 'Dashboard'; // For permissions

    public function __construct(Database $db, array $config = [])
    {
        parent::__construct($db, $config);
        // No specific models needed if using direct DB queries for simple stats
    }

    /**
     * Show the main application dashboard.
     * Requires user to be logged in (handled by BaseController::before).
     */
    public function index(): void
    {
        $this->actionName = 'index';
        // No specific permission check usually needed beyond being logged in

        // --- Fetch Dashboard Data ---
        // Use try-catch for database operations
        try {
            // Project Stats
            $totalProjects = $this->db->selectValue("SELECT COUNT(*) FROM projects") ?? 0;
            $activeProjects = $this->db->selectValue("SELECT COUNT(*) FROM projects WHERE status = 'active'") ?? 0;
            $activeProjectsContractAmount = $this->db->selectValue("SELECT COALESCE(SUM(contract_amount), 0) FROM projects WHERE status = 'active'") ?? 0.0;

            // Billing Stats (Assuming 'billings' and 'billing_details' tables)
            // Note: These queries might become slow on large datasets. Consider optimization or summary tables.
            $draftBillings = $this->db->selectValue("SELECT COUNT(*) FROM billings WHERE status = 'draft'") ?? 0;
            $submittedBillings = $this->db->selectValue("SELECT COUNT(*) FROM billings WHERE status = 'submitted'") ?? 0;
            $approvedBillings = $this->db->selectValue("SELECT COUNT(*) FROM billings WHERE status = 'approved'") ?? 0;

            // Total Approved Amount (More complex query - ensure correctness)
            // This assumes a billing_details table linked to billings
            $totalApprovedBillingsAmount = $this->db->selectValue(
                "SELECT COALESCE(SUM(bd.total_earned), 0)
                 FROM billings b
                 JOIN (
                     SELECT
                         billing_id,
                         SUM(COALESCE(work_completed_this_period, 0) + COALESCE(materials_stored_this_period, 0)) as total_earned
                     FROM billing_details
                     GROUP BY billing_id
                 ) bd ON b.id = bd.billing_id
                 WHERE b.status = 'approved'"
            ) ?? 0.0;

            // Total Paid Amount (Assuming a field like 'amount_paid' or similar on billings table)
            // Using 'total_earned_less_retainage' might not represent actual payment received.
            // Let's assume a 'payments' table or 'amount_paid' field for a more accurate representation.
            // Placeholder: Using approved amount for now, adjust based on actual payment tracking.
            $totalPaidBillingsAmount = $this->db->selectValue("SELECT COALESCE(SUM(amount_paid), 0) FROM billings WHERE status = 'paid'") ?? 0.0; // Adjust field/table as needed
            $totalCurrentPaymentDues = $totalApprovedBillingsAmount - $totalPaidBillingsAmount; // This is an approximation

            // Total Retainage Held (Complex - sum of retainage per billing)
            // This requires calculating retainage for each billing detail line based on its billing's rate
             $totalRetainageHeld = $this->db->selectValue(
                 "SELECT COALESCE(SUM(bd.retainage_held), 0)
                  FROM billings b
                  JOIN (
                      SELECT
                          billing_id,
                          SUM(
                              (COALESCE(work_completed_previous, 0) + COALESCE(work_completed_this_period, 0)) * COALESCE(b.retainage_rate_work, b.retainage_rate, 0) / 100 +
                              (COALESCE(materials_stored_previous, 0) + COALESCE(materials_stored_this_period, 0)) * COALESCE(b.retainage_rate_stored, b.retainage_rate, 0) / 100
                          ) as retainage_held
                      FROM billing_details bd
                      JOIN billings b_inner ON bd.billing_id = b_inner.id -- Join to get rates if needed per billing
                      GROUP BY billing_id
                  ) bd ON b.id = bd.billing_id"
             ) ?? 0.0; // This query needs careful review based on exact schema and retainage calculation logic


        } catch (\PDOException $e) {
            error_log("Database Error in DashboardController::index: " . $e->getMessage());
            // Set default values or show error message
            $totalProjects = $activeProjects = $draftBillings = $submittedBillings = $approvedBillings = 0;
            $activeProjectsContractAmount = $totalCurrentPaymentDues = $totalRetainageHeld = 0.0;
            $this->setFlashMessage('error', 'Could not load all dashboard data due to a database error.');
        }
        // --- End Fetch Data ---


        // Set data for the view
        $viewData = [
            'pageTitle' => 'Dashboard',
            'activeNav' => 'dashboard', // For highlighting nav item
            'totalProjects' => $totalProjects,
            'activeProjects' => $activeProjects,
            'activeProjectsContractAmount' => $activeProjectsContractAmount,
            'draftBillings' => $draftBillings,
            'submittedBillings' => $submittedBillings,
            'approvedBillings' => $approvedBillings,
            'totalCurrentPaymentDues' => $totalCurrentPaymentDues,
            'totalRetainage' => $totalRetainageHeld, // Use calculated value
            // Add more data as needed (e.g., recent activity feed)
        ];

        // Render the dashboard view
        $this->render('dashboard', $viewData); // Use .php
    }
}