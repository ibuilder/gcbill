<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Models\ChangeOrder as AppModelsChangeOrder;
use App\Database;

class ChangeOrdersController
{
    protected $controller = 'ChangeOrders';
    protected $action;
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Method called before any action.
     * Checks if the user is logged in and has permission to access the current controller and action.
     * @param string $action The current action name.
     * @return void
     */
    protected function before(string $action): void
    {
        $this->action = $action;
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
    /**
     * Magic method to handle invalid actions.
     * @param string $method The method name.
     * @param array $args The arguments.
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

    /**
     * Index action: Show all change orders for a given project ID.
     * @param int $projectId The project ID.
     * @return string
     */

    public function index(int $projectId): string
    {
        $this->before('index');
        try {
            $changeOrder = new AppModelsChangeOrder($this->db);
            $changeOrders = $changeOrder->all($projectId);
            return json_encode($changeOrders);


        } catch (\Exception $e) {
            error_log('Error in ChangeOrdersController::index: ' . $e->getMessage());
            return json_encode(['error' => 'An error occurred while processing your request.']);
        }
    }

    /**
     * View action: Show a specific change order.
     * @param int $id The change order ID.
     * @return string
     */
    public function view(int $id): string
    {
        $this->before('view');
        try {
            $changeOrder = new AppModelsChangeOrder($this->db);
            $result = $changeOrder->find($id);
            if (!$result) {
                return json_encode(['error' => 'Change order not found.']);
            } catch (\Exception $e) {
                error_log('Error in ChangeOrdersController::view: ' . $e->getMessage());
                 return json_encode(['error' => 'An error occurred while processing your request.']);
            }

            return json_encode($result);
           
        } catch (\Exception $e) {
             error_log('Error in ChangeOrdersController::view: ' . $e->getMessage());
            return json_encode(['error' => 'An error occurred while processing your request.']);
        }
        
    }

    /**
     * Create action: Create a new change order.
     * @param array $data The change order data.
     * @return string
     */
    public function create(array $data): string
    {
        $this->before('create');
        try{
            $changeOrder = new AppModelsChangeOrder($this->db);
            $result = $changeOrder->insert($data);
            if (!$result) {
                return json_encode(['error' => 'Error creating change order.']);
            }
            return json_encode(['id' => $result]);
        }catch(\Exception $e){
            error_log('Error in ChangeOrdersController::create: ' . $e->getMessage());
            return json_encode(['error' => 'An error occurred while processing your request.']);
        }
    }