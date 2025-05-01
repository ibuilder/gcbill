php
<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Models\Project;
use App\Models\BillingPeriod;
use App\Models\SovBillingItem;
use App\Models\GeneralCondition;
use App\Models\ChangeOrder;
use App\Models\Staff;
use App\Models\StaffTimeEntry;



class ReportController extends Controller
{
    protected function before()
    {
        // Check if the user is logged in
        if (!Auth::isLoggedIn()) {
            // Redirect to the login page if not logged in
            header('Location: /login');
            exit;
        }

        // Get the current user from the session
        $user = $_SESSION['user'];

        // Check if the user has permission to access the current controller and action
        if (!Auth::checkPermission($user, $this->controller, $this->action)) {
            // Redirect to a 403 error page if no permission
            header('Location: /403');
            exit;
        }
    }
    
    public function projectCostSummary($projectId)
    {
        $this->before();

        // Load the models
        $projectModel = new Project();
        $billingPeriodModel = new BillingPeriod();
        $sovBillingItemModel = new SovBillingItem();
        $generalConditionModel = new GeneralCondition();
        $changeOrderModel = new ChangeOrder();

        // Get the project data
        $project = $projectModel->find($projectId);
        if (!$project) {
            // Handle project not found (e.g., redirect to an error page)
            header('Location: /404');
            exit;
        }

        // Get billing periods for the project
        $billingPeriods = $billingPeriodModel->where('project_id', $projectId)->get();

        // Calculate total billed and retainage
        $totalBilled = 0;
        $totalRetainage = 0;
        foreach ($billingPeriods as $period) {
            $billedItems = $sovBillingItemModel->where('billing_period_id', $period['id'])->get();
            foreach($billedItems as $item){
                $totalBilled += ($item['work_completed_this_period'] + $item['materials_stored_this_period']);
            }
            // Calculate retainage for the period (example calculation)
            $totalRetainage += $totalBilled * ($project['retainage_percentage'] / 100); 
        }

        // Get general conditions for the project
        $generalConditions = $generalConditionModel->where('project_id', $projectId)->get();
        $totalGeneralConditionsCost = array_sum(array_column($generalConditions, 'actual_cost_to_date'));

        // Get change orders for the project
        $changeOrders = $changeOrderModel->where('project_id', $projectId)->get();
        $totalChangeOrdersAmount = 0;
        foreach($changeOrders as $order){
            $totalChangeOrdersAmount += $order['amount'];
        }

        $data = compact('project', 'totalBilled', 'totalRetainage', 'totalGeneralConditionsCost', 'totalChangeOrdersAmount');

        // Pass the data to the view
        $this->view('reports/projectCostSummary', $data);
    }

    public function billingsPerProject($projectId)
    {
        $this->before();

        // Load the models
        $projectModel = new Project();
        $billingPeriodModel = new BillingPeriod();
        $sovBillingItemModel = new SovBillingItem();

        // Get the project data
        $project = $projectModel->find($projectId);
        if (!$project) {
            // Handle project not found (e.g., redirect to an error page)
            header('Location: /404');
            exit;
        }

        // Get billing periods for the project
        $billingPeriods = $billingPeriodModel->where('project_id', $projectId)->get();
        
        $billingsData = [];
        // Calculate total billed and retainage
        foreach ($billingPeriods as $period) {
            $billedItems = $sovBillingItemModel->where('billing_period_id', $period['id'])->get();
            $totalBilled = 0;
            foreach($billedItems as $item){
                $totalBilled += ($item['work_completed_this_period'] + $item['materials_stored_this_period']);
            }
            $billingsData[] = [
                'period' => $period,
                'billed_amount' => $totalBilled
            ];
        }

        $data = compact('project', 'billingsData');
        // Pass the data to the view
        $this->view('reports/billingsPerProject', $data);
    }

    public function retainageReport($projectId)
    {
        $this->before();
        // Load the models
        $projectModel = new Project();
        $billingPeriodModel = new BillingPeriod();

        // Get the project data
        $project = $projectModel->find($projectId);
        if (!$project) {
            // Handle project not found (e.g., redirect to an error page)
            header('Location: /404');
            exit;
        }

        // Get billing periods for the project
        $billingPeriods = $billingPeriodModel->where('project_id', $projectId)->get();
        
        $totalBilled = 0;
        $totalRetainage = 0;
        foreach ($billingPeriods as $period) {
            $billedItems = (new SovBillingItem())->where('billing_period_id', $period['id'])->get();
            foreach($billedItems as $item){
                $totalBilled += ($item['work_completed_this_period'] + $item['materials_stored_this_period']);
            }
        }
        
        $totalRetainage = $totalBilled * ($project['retainage_percentage'] / 100);

        $data = compact('project', 'totalBilled', 'totalRetainage');
        // Pass the data to the view
        $this->view('reports/retainageReport', $data);
    }

    public function staffTimeTracking($projectId, $startDate, $endDate)
    {
        $this->before();

         // Load the models
         $projectModel = new Project();
         $staffModel = new Staff();
         $staffTimeEntryModel = new StaffTimeEntry();
 
         // Get the project data
         $project = $projectModel->find($projectId);
         if (!$project) {
             // Handle project not found (e.g., redirect to an error page)
             header('Location: /404');
             exit;
         }
 
         // Get the staff members
         $staffMembers = $staffModel->all();
 
         // Get staff time entries for the project and date range
         $timeEntries = $staffTimeEntryModel
             ->where('project_id', $projectId)
             ->where('entry_date', '>=', $startDate)
             ->where('entry_date', '<=', $endDate)
             ->get();
        
        $staffData = [];
        foreach($staffMembers as $staff){
            $staffData[] = [
                'staff' => $staff,
                'time_entries' => array_filter($timeEntries, fn($entry) => $entry['staff_id'] === $staff['id'])
            ];
        }
         $data = compact('project', 'staffData', 'startDate', 'endDate');
        $this->view('reports/staffTimeTracking', $data);
    }
}
