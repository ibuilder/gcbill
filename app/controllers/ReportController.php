<?php

namespace AppControllers;
use App\Controllers\BaseController;
use App\View; 
use App\Database;
use App\Models\Project; 
use App\Models\BillingPeriod; 
use App\Models\SOV as SovBillingItem; 
use App\Models\GeneralCondition; 
use App\Models\ChangeOrder; 
use App\Models\Staff; 
use App\Models\StaffTimeEntry; 
class ReportController extends BaseController 
{

    private $db;
    private $view;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->view = new View();
    }

    public function projectCostSummary($projectId)
    {
        $this->before();


        // Load the models
        $projectModel = new Project($this->db);
        $billingPeriodModel = new BillingPeriod($this->db);
        $sovBillingItemModel = new SovBillingItem($this->db);
        $generalConditionModel = new GeneralCondition($this->db);
        $changeOrderModel = new ChangeOrder($this->db);

        // Get the project data
        $project = $projectModel->find($projectId);
        if (!$project) {
            // Handle project not found (e.g., redirect to an error page)
            //header('Location: /404');
            exit;
        }

        // Get billing periods for the project
        $billingPeriods = $billingPeriodModel->where('project_id', $projectId)->get();

        // Calculate total billed and retainage
        $totalBilled = 0;
        $totalRetainage = 0;
        foreach ($billingPeriods as $period) {
            $billedItems = $sovBillingItemModel->where('billing_period_id', $period['id'])->get();
            foreach ($billedItems as $item) {
                $totalBilled += ($item['work_completed_this_period'] + $item['materials_stored_this_period']);
            }

            $totalRetainage += $totalBilled * ($project['retainage_percentage'] / 100);
        }

        // Get general conditions for the project
        $generalConditions = $generalConditionModel->where('project_id', $projectId)->get();
        $totalGeneralConditionsCost = array_sum(array_column($generalConditions, 'actual_cost_to_date'));

        // Get change orders for the project
        $changeOrders = $changeOrderModel->where('project_id', $projectId)->get(); 
        $totalChangeOrdersAmount= 0;
        foreach ($changeOrders as $order) {
            $totalChangeOrdersAmount += $order['amount'];
        }

        $data = compact('project', 'totalBilled', 'totalRetainage', 'totalGeneralConditionsCost', 'totalChangeOrdersAmount');

        // Pass the data to the view
        $this->view->render('reports/projectCostSummary.html', $data);

    }

    public function billingsPerProject($projectId)
    {
        $this->before();

        // Load the models
        $projectModel = new Project($this->db);
        $billingPeriodModel = new BillingPeriod($this->db);
        $sovBillingItemModel = new SovBillingItem($this->db);

        // Get the project data
        $project = $projectModel->find($projectId);
        if (!$project) {
            // Handle project not found (e.g., redirect to an error page)
            //header('Location: /404');
            exit;
        }

        // Get billing periods for the project
        $billingPeriods = $billingPeriodModel->where('project_id', $projectId)->get();

        $billingsData = [];
        // Calculate total billed and retainage
        foreach ($billingPeriods as $period) {
            $billedItems = $sovBillingItemModel->where('billing_period_id', $period['id'])->get();
            $totalBilled = 0;
            foreach ($billedItems as $item) {
                $totalBilled += ($item['work_completed_this_period'] + $item['materials_stored_this_period']);
            }
            $billingsData[] = [
                'period' => $period,
                'billed_amount' => $totalBilled
            ];
        }

        $data = compact('project', 'billingsData');
        // Pass the data to the view
        $this->view->render('reports/billingsPerProject.html', $data);
    }

    public function retainageReport($projectId)
    {
        $this->before();
        // Load the models
        $projectModel = new Project($this->db);
        $billingPeriodModel = new BillingPeriod($this->db);

        // Get the project data
        $project = $projectModel->find($projectId);
        if (!$project) {
            // Handle project not found (e.g., redirect to an error page)
            //header('Location: /404');
            exit;
        }

        // Get billing periods for the project
        $billingPeriods = $billingPeriodModel->where('project_id', $projectId)->get();

        $totalBilled = 0;
        $totalRetainage = 0;
        foreach ($billingPeriods as $period) {
            $billedItems = (new SovBillingItem())->where('billing_period_id', $period['id'])->get();
            foreach ($billedItems as $item) {
                $totalBilled += ($item['work_completed_this_period'] + $item['materials_stored_this_period']);
            }
        }

        $totalRetainage = $totalBilled * ($project['retainage_percentage'] / 100);

        $data = compact('project', 'totalBilled', 'totalRetainage');
        // Pass the data to the view
        $this->view->render('reports/retainageReport.html', $data);
    }

    public function staffTimeTracking($projectId, $startDate, $endDate)
    {
        $this->before();

        // Load the models
        $projectModel = new Project($this->db);
        $staffModel = new Staff($this->db);
        $staffTimeEntryModel = new StaffTimeEntry();

        // Get the project data
        $project = $projectModel->find($projectId);
        if (!$project) {
            // Handle project not found (e.g., redirect to an error page)
            // header('Location: /404');
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
        foreach ($staffMembers as $staff) {
            $staffData[] = [
                'staff' => $staff,
                'time_entries' => array_filter($timeEntries, fn ($entry) => $entry['staff_id'] === $staff['id'])
            ];
        }
        $data = compact('project', 'staffData', 'startDate', 'endDate');
        // Pass the data to the view
        $this->view->render('reports/staffTimeTracking.html', $data);

    }    

}