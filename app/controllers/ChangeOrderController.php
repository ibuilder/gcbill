php
<?php

namespace App\Controllers;

use App\Models\ChangeOrder;
use App\Models\ChangeOrderItem;
use App\Libraries\Auth;
use App\Database;

class ChangeOrderController
{
    private $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    private function before($action)
    {
        // Check if the user is logged in
        if (!$this->auth->isLoggedIn()) {
            // Redirect to login page
            header('Location: /login');
            exit;
        }

        // Check for permissions
        $user = $this->auth->getCurrentUser();
        if (!$this->auth->checkPermission($user, 'ChangeOrderController', $action)) {
            // Redirect to 403 error page
            header('HTTP/1.1 403 Forbidden');
            echo "<h1>403 Forbidden</h1>";
            exit;
        }
    }
    public function index($project_id)
    {
        // 1. Get the project id from the parameters.
        $projectId = $project_id;

        // 2. Use a database query to get all the change orders that have the received project_id.
        $db = Database::getInstance();
        $query = "SELECT * FROM change_orders WHERE project_id = :project_id";
        $params = [':project_id' => $projectId];
        $results = $db->query($query, $params);

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
    }
    



    public function view($id)
    {
        // 1. Get the change order ID from the parameters.
        $changeOrderId = $id;

        // 2. Use a database query to get the change order that has the received ID.
        $db = Database::getInstance();
        $query = "SELECT * FROM change_orders WHERE id = :id";
        $params = [':id' => $changeOrderId];
        $result = $db->query($query, $params);
        $changeOrder = new ChangeOrder($result[0]);

        // 3. Use a database query to get all the items that have the change order id.
        $query = "SELECT * FROM change_order_items WHERE change_order_id = :change_order_id";
        $params = [':change_order_id' => $changeOrderId];
        $results = $db->query($query, $params);

        // 4. Create an array with all the change order items.
        $changeOrderItems = [];
        foreach ($results as $result) {
            $changeOrderItem = new ChangeOrderItem($result);
            $changeOrderItems[] = $changeOrderItem->toArray();
        }

        // 5. Return a string with the change order and all the change order items in json format.
        return json_encode(['change_order' => $changeOrder->toArray(), 'change_order_items' => $changeOrderItems]);
    }

    


    public function create($data)
    {
        // 1. Receive the data in an array as a parameter.
        $changeOrderData = $data;

        // 2. Create a new `ChangeOrder` object with the data received.
        $changeOrder = new ChangeOrder($changeOrderData);

        // 3. Add the change order to the database.
        $db = Database::getInstance();
        $query = "INSERT INTO change_orders (project_id, change_order_number, description, change_order_date, status) VALUES (:project_id, :change_order_number, :description, :change_order_date, :status)";
        $params = $changeOrder->toArray();
        $db->query($query, $params);

        // 4. Return a string with the id of the new change order.
        return json_encode(['id' => $db->lastInsertId()]);
    }

   
    public function edit($id, $data)
    {
        // 1. Receive the change order id as the first parameter.
        $changeOrderId = $id;

        // 2. Receive the data to update in an array as the second parameter.
        $changeOrderData = $data;

        // 3. Get the change order from the database with the received id.
        $db = Database::getInstance();
        $query = "SELECT * FROM change_orders WHERE id = :id";
        $params = [':id' => $changeOrderId];
        $result = $db->query($query, $params);
        $changeOrder = new ChangeOrder($result[0]);

        // 4. Update the change order data with the data received in the array.
        $changeOrder->fromArray($changeOrderData);

        // 5. Update the database.
        $query = "UPDATE change_orders SET project_id = :project_id, change_order_number = :change_order_number, description = :description, change_order_date = :change_order_date, status = :status WHERE id = :id";
        $params = $changeOrder->toArray();
        $db->query($query, $params);

        // 6. Return a string with the id of the updated change order.
        return json_encode(['id' => $changeOrderId]);
    }

    
    public function delete($id)
    {
        // 1. Receive the change order id as a parameter.
        $changeOrderId = $id;

        // 2. Get the change order from the database with the received id.
        $db = Database::getInstance();
        $query = "SELECT * FROM change_orders WHERE id = :id";
        $params = [':id' => $changeOrderId];
        $result = $db->query($query, $params);
        $changeOrder = new ChangeOrder($result[0]);

        // 3. Delete the change order from the database.
        $query = "DELETE FROM change_orders WHERE id = :id";
        $params = [':id' => $changeOrderId];
        $db->query($query, $params);

        // 4. Return a string with the id of the deleted change order.
        return json_encode(['id' => $changeOrderId]);

    }
    
    public function __call($method, $args)
    {
        // Check if the requested action is valid
        if (method_exists($this, $method)) {
             $this->before($method);

            return call_user_func_array([$this, $method], $args);
        } else {
            header('HTTP/1.1 404 Not Found');
        }
    }
}