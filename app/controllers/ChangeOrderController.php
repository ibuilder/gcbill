<?php

namespace App\Controllers;

use App\Models\ChangeOrder;
use App\Models\ChangeOrderItem;
use Exception;
use App\Libraries\Auth; // Correct the class name
use App\Database;

class ChangeOrderController
{
    protected $controller = 'ChangeOrder';
    protected $action;    
    protected $db;

    private Auth $auth;

    public function __construct(Database $db)
    {      
        $this->auth = new Auth();
        $this->db = $db;
    }

    protected function before($action)
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
     * Index action: Get all change orders for a given project ID.
     * @param int $project_id The project ID.
     * @return string JSON encoded data with project_id and change orders.
     */
    public function index(int $project_id): string
    {
        $this->before('index');
        try{
            
            // 1. Get the project id from the parameters.
            $projectId = $project_id;

            // 2. Use a database query to get all the change orders that have the received project_id.
            $query = "SELECT * FROM change_orders WHERE project_id = :project_id";
            $params = [':project_id' => $projectId];
            $results = $this->db->query($query, $params);

            // 3. Create an array with all the change orders.
            $changeOrders = [];
            foreach ($results as $result) {
                $changeOrder = new ChangeOrder($result);
                $changeOrders[] = $changeOrder->toArray();
            }

            // 4. Return a string with the project_id, and all the change orders in a json format.
            return json_encode([
                'project_id' => $projectId,
                'change_orders' => $changeOrders
            ]);
        }catch (Exception $e){
            error_log('Error in ChangeOrderController::index: ' . $e->getMessage());
            return json_encode(['error' => 'An error occurred while processing your request.']);
        }
    }
    

    /**
     * View action: Get a specific change order and its items.
     * @param int $id The change order ID.
     * @return string JSON encoded data with the change order and its items.
     */
    public function view(int $id): string
    {
        $this->before('view');
        // 1. Get the change order ID from the parameters.
        $changeOrderId = $id;

        // 2. Use a database query to get the change order that has the received ID.
        $query = "SELECT * FROM change_orders WHERE id = :id";
        $params = [':id' => $changeOrderId];
        $result = $this->db->query($query, $params);
        $changeOrder = new ChangeOrder($result[0]);

        // 3. Use a database query to get all the items that have the change order id.
        $query = "SELECT * FROM change_order_items WHERE change_order_id = :change_order_id";
        $params = [':change_order_id' => $changeOrderId];
        $results = $this->db->query($query, $params);

        // 4. Create an array with all the change order items.
        $changeOrderItems = [];
        foreach ($results as $result) {
            $changeOrderItem = new ChangeOrderItem($result);
            $changeOrderItems[] = $changeOrderItem->toArray();
        }

        // 5. Return a string with the change order and all the change order items in json format.
        return json_encode(['change_order' => $changeOrder->toArray(), 'change_order_items' => $changeOrderItems]);
    }

    

     /**
     * Create action: Create a new change order.
     * @param array $data The change order data.
     * @return string JSON encoded data with the id of the new change order.
     */
    public function create(array $data): string
    {
        $this->before('create');        
        try {
            // 1. Receive the data in an array as a parameter.
            $changeOrderData = $data;

            // 2. Create a new `ChangeOrder` object with the data received.
            $changeOrder = new ChangeOrder($changeOrderData);

            // 3. Add the change order to the database.
            $query = "INSERT INTO change_orders (project_id, change_order_number, description, change_order_date, status) VALUES (:project_id, :change_order_number, :description, :change_order_date, :status)";
            $params = $changeOrder->toArray();
            $this->db->query($query, $params);

            // 4. Return a string with the id of the new change order.
            return json_encode(['id' => $this->db->lastInsertId()]);
        } catch (Exception $e) {
            // Log the error
            error_log('Error in ChangeOrderController::create: ' . $e->getMessage());
            // Return a JSON error response
            return json_encode(['error' => 'An error occurred while processing your request.']);
        }
    }

   
    /**
     * Edit action: Update an existing change order.
     * @param int $id The change order ID.
     * @param array $data The data to update.
     * @return string JSON encoded data with the id of the updated change order.
     */
    public function edit(int $id, array $data): string
    {
        $this->before('edit');
        try {
            // 1. Receive the change order id as the first parameter.
            $changeOrderId = $id;

            // 2. Receive the data to update in an array as the second parameter.
            $changeOrderData = $data;

            // 3. Get the change order from the database with the received id.
            $query = "SELECT * FROM change_orders WHERE id = :id";
            $params = [':id' => $changeOrderId];
            $result = $this->db->query($query, $params);
            $changeOrder = new ChangeOrder($result[0]);

            // 4. Update the change order data with the data received in the array.
            $changeOrder->fromArray($changeOrderData);

            // 5. Update the database.
            $query = "UPDATE change_orders SET project_id = :project_id, change_order_number = :change_order_number, description = :description, change_order_date = :change_order_date, status = :status WHERE id = :id";
            $params = $changeOrder->toArray();
            $this->db->query($query, $params);
        } catch (Exception $e) {
            // Log the error
            error_log('Error in ChangeOrderController::edit: ' . $e->getMessage());
        }

        // 6. Return a string with the id of the updated change order.
        return json_encode(['id' => $changeOrderId]);
    }

    /**
     * Delete action: Delete a change order.
     * @param int $id The change order ID.
     * @return string JSON encoded data with the id of the deleted change order.
     */
    public function delete(int $id): string
    {
        $this->before('delete');
        try {
            // 1. Receive the change order id as a parameter.
            $changeOrderId = $id;

            // 2. Get the change order from the database with the received id.
            $query = "SELECT * FROM change_orders WHERE id = :id";
            $params = [':id' => $changeOrderId];
            $result = $this->db->query($query, $params);

            if (empty($result)) {
                // Return a JSON error response if change order not found
                return json_encode(['error' => 'Change order not found.']);
            }

            $changeOrder = new ChangeOrder($result[0]);

            // 3. Delete the change order from the database.
            $query = "DELETE FROM change_orders WHERE id = :id";
            $params = [':id' => $changeOrderId];
            $this->db->query($query, $params);

            // 4. Return a string with the id of the deleted change order.
            return json_encode(['id' => $changeOrderId]);
        } catch (Exception $e) {
            // Log the error
            error_log('Error in ChangeOrderController::delete: ' . $e->getMessage());
            // Return a JSON error response
            return json_encode(['error' => 'An error occurred while processing your request.']);
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

            return call_user_func_array([$this, $method], $args);
        } else {
            header('HTTP/1.1 404 Not Found');
        }
    }
}