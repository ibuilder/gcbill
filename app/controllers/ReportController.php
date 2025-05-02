<?php

namespace App\Controllers; // Correct namespace

use App\Database;
use App\Models\Project;
use App\Models\Billing; // Assuming Billing model handles periods/details
use App\Models\Staff;
use App\Models\StaffTimeEntry;
// Add other models as needed: ChangeOrder, GeneralCondition, etc.

class ReportController extends BaseController
{
    protected string $controllerName = 'Report'; // For permissions

    // Models can be loaded via loadModel() or instantiated here if always needed
    private Project $projectModel;
    private Billing $billingModel;
    private Staff $staffModel;
    private StaffTimeEntry $timeEntryModel;

    public function __construct(Database $db, array $config = [])
    {
        parent::__construct($db, $config);
        // Instantiate models
        $this->projectModel = new Project($this->db);
        $this->billingModel = new Billing($this->db); // Assuming Billing model exists
        $this->staffModel = new Staff($this->db);
        $this->timeEntryModel = new StaffTimeEntry($this->db); // Assuming model exists
    }

    // Example: Project Cost Summary Report (needs more detailed data fetching)
    public function projectCostSummary(): void // Removed $projectId, maybe filter via GET?
    {
        $this->actionName = 'projectCostSummary';
        $this->requirePermission();

        // TODO: Implement filtering (e.g., by project status, date range) via GET parameters
        // TODO: Fetch comprehensive cost data for each project
        // This likely requires complex queries or multiple model calls per project
        // - Original Budget (from project)
        // - Approved Changes (sum from change orders)
        // - Current Budget
        // - Committed Costs (from purchase orders, subcontracts - requires models)
        // - Actual Costs to Date (sum from expenses, time entries, etc. - requires models)
        // - Projected Costs (complex calculation)
        // - Variance

        $projects = $this->projectModel->findAllWithCostSummary(); // Ideal: Model method aggregates data

        $this->render('reports/projectCostSummary', [ // Use .php
            'pageTitle' => 'Project Cost Summary Report',
            'activeNav' => 'report-cost-summary',
            'reportData' => $projects // Pass the aggregated data
        ]);
    }

    // Example: Billings Per Project Report
    public function billingsPerProject(): void // Removed $projectId, show all or filter?
    {
        $this->actionName = 'billingsPerProject';
        $this->requirePermission();

        // TODO: Implement filtering (project, date range) via GET parameters

        // Fetch projects and their associated billings
        // This might involve joining tables or multiple queries
        $reportData = $this->projectModel->findAllWithBillingsSummary(); // Ideal: Model method aggregates

        $this->render('reports/billingsPerProject', [ // Use .php
            'pageTitle' => 'Billings Per Project Report',
            'activeNav' => 'report-billings-project',
            'reportData' => $reportData // Should be structured as [ProjectName => [BillingInfo...]]
        ]);
    }

    // Example: Retainage Report
    public function retainageReport(): void // Removed $projectId, show all or filter?
    {
        $this->actionName = 'retainageReport';
        $this->requirePermission();

        // TODO: Implement filtering (project, date range) via GET parameters

        // Fetch projects and calculate retainage held/released
        // Requires querying projects and billings, potentially payments
        $reportData = $this->projectModel->findAllWithRetainageSummary(); // Ideal: Model method aggregates

        $this->render('reports/retainageReport', [ // Use .php
            'pageTitle' => 'Retainage Report',
            'activeNav' => 'report-retainage',
            'reportData' => $reportData
        ]);
    }

    // Example: Staff Time Tracking Report
    public function staffTimeTracking(): void // Removed params, use GET for filtering
    {
        $this->actionName = 'staffTimeTracking';
        $this->requirePermission();

        // Get filter parameters from GET request
        $filters = [
            'project_id' => filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT) ?: null,
            'staff_id' => filter_input(INPUT_GET, 'staff_id', FILTER_VALIDATE_INT) ?: null,
            'start_date' => filter_input(INPUT_GET, 'start_date') ?: null,
            'end_date' => filter_input(INPUT_GET, 'end_date') ?: null,
        ];

        // Fetch time entries based on filters
        $timeEntries = $this->timeEntryModel->findWithDetailsByFilters($filters); // Model method handles filtering and joins

        // Fetch data for filter dropdowns
        $projects = $this->projectModel->findAllSimple(); // id, name
        $staffMembers = $this->staffModel->findAllSimple(); // id, name

        $this->render('reports/staffTimeTracking', [ // Use .php
            'pageTitle' => 'Staff Time Tracking Report',
            'activeNav' => 'report-time-tracking',
            'reportData' => $timeEntries,
            'filters' => $filters, // Pass filters back to view
            'projects' => $projects,
            'staffMembers' => $staffMembers
        ]);
    }
}