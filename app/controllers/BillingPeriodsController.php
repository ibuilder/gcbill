<?php
namespace App\Controllers;

use App\Libraries\Auth;
use App\Models\BillingPeriod;
use App\Database;

class BillingPeriodsController extends BaseController
{
    protected string $controller = 'BillingPeriods';
    protected string $action;

    /**
     * Constructor for the BillingPeriodsController class.
     *
     * @param Database $db The database instance.
     */
    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    public function before(string $action): void
    {
        $this->action = $action;
        // Check if the user is logged in
        if (!Auth::isLoggedIn())
        {
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

    /**
     * Index action: Show all billing periods for a given project ID.
     *
     * @param integer $projectId The project ID.
     * @return string JSON encoded data with the project ID and billing periods.
     */
    public function index(int $projectId): string
    {
        $this->before('index');
        try {
            $billingPeriod = new BillingPeriod($this->db);
            $billingPeriods = $billingPeriod->where('project_id', $projectId)->get();
            return json_encode(['project_id' => $projectId, 'billing_periods' => $billingPeriods]);
        } catch (\Exception $e) {
            error_log('Error in BillingPeriodsController::index: ' . $e->getMessage());
            return json_encode(['error' => 'An error occurred while processing your request.']);
        }
    }
    
    /**
     * View action: Show a specific billing period.
     * @param int $id The billing period ID.
     * @return string JSON encoded data with the billing period.
     */
    public function view(int $id): string
    {
        $this->before('view');
        try {
            $billingPeriod = new BillingPeriod($this->db);
            $result = $billingPeriod->find($id);
            if (!$result) {
                return json_encode(['error' => 'Billing period not found.']);
            }
            return json_encode(['billing_period' => $result]);
        } catch (\Exception $e) {
            error_log('Error in BillingPeriodsController::view: ' . $e->getMessage());
            return json_encode(['error' => 'An error occurred while processing your request.']);
        }
    }    


    /**
     * Create action: Create a new billing period.
     * @param array $data The billing period data.
     * @return string JSON encoded data with the ID of the new billing period.
     */
    public function create(array $data): string
    {
        $this->before('create');        
        try {
            $billingPeriod = new BillingPeriod($this->db);
            $result = $billingPeriod->insert($data);
            if (!$result) {
                return json_encode(['error' => 'An error occurred while creating the billing period.']);
            }
            return json_encode(['id' => $result]);
        } catch (\Exception $e) {
            error_log('Error in BillingPeriodsController::create: ' . $e->getMessage());
            
            return json_encode(['error' => 'An error occurred while processing your request.']);
        }
    }    

    /**
     * Edit action: Update an existing billing period.
     * @param int $id The billing period ID.
     * @param array $data The billing period data.
     * @return string JSON encoded data with the ID of the updated billing period.
     */
    public function edit(int $id, array $data): string
    {
        $this->before('edit');
        try {
            $billingPeriod = new BillingPeriod($this->db);
            $result = $billingPeriod->update($id, $data);
            if ($result === -1) {
                return json_encode(['error' => 'Billing period not found.']);
            }
            if (!$result) {
                return json_encode(['error' => 'An error occurred while updating the billing period.']);
            }
            
            return json_encode(['id' => $id]);
        }catch(Exception $e){
            error_log('Error in BillingPeriodsController::edit: ' . $e->getMessage());
            return json_encode(['error' => 'An error occurred while processing your request.']);
        }
    }    

    /**
     * Delete action: Delete a billing period.
     * @param int $id The billing period ID.
     * @return string JSON encoded data with the ID of the deleted billing period.
     */
    public function delete(int $id): string
    {
        $this->before('delete');
        try {
            $billingPeriod = new BillingPeriod($this->db);
            $result = $billingPeriod->remove($id);
            if ($result === -1) {
                return json_encode(['error' => 'Billing period not found.']);
            }
            if (!$result) {
                return json_encode(['error' => 'An error occurred while deleting the billing period.']);
            }
            return json_encode(['id' => $id]);
        } catch (\Exception $e) {
        }catch(Exception $e){
            error_log('Error in BillingPeriodsController::delete: ' . $e->getMessage());
            return json_encode(['error' => 'An error occurred while processing your request.']);
        }
    }
        
    /**
     * Magic method to handle invalid actions
     * @param string $method
     * @param array $args
     * @return void
     */
    public function __call(string $method, array $args): void
    {
        // Check if the requested action is valid
        if (method_exists($this, $method)) {
            call_user_func_array([$this, $method], $args);
        } else {
            header('HTTP/1.1 404 Not Found');
        }
    }

}