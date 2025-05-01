<?php
// filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\app\Controllers\DashboardController.php
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
        $projectCount = $this->db->selectValue("SELECT COUNT(*) FROM projects");
        $activeStaffCount = $this->db->selectValue("SELECT COUNT(*) FROM staff WHERE is_active = 1");

        // Set data for the view
        $viewData = [
            'pageTitle' => 'Dashboard',
            'activeNav' => 'dashboard', // For highlighting nav item
            'projectCount' => $projectCount ?? 0,
            'activeStaffCount' => $activeStaffCount ?? 0,
            // Add more data as needed
        ];

        // Render the dashboard view
        $this->view->output('dashboard.html', $viewData);
    }
}