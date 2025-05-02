<?php

namespace App\Controllers; // Changed namespace

use App\Libraries\Auth;
use App\Database;
use App\Models\Owner;

class OwnerController extends BaseController // Changed class name
{
    protected $controller = 'Owners';
    protected $db;

    /**
     * Constructor for the OwnersController class.
     * @param Database $db The database instance.
     */
    public function __construct(Database $db)

    {
        $this->db = $db;
    }

    public function before(string $action)
    {
        /**
         * Method called before any action.
         * Checks if the user is logged in and has permission to access the current controller and action.
         * @param string $action The current action name.
         * @return void
         */
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
     * Index action: Show all owners.
     * @return string
     */
    public function index(): string
    {
        $this->before('index');
        try {
            $owner = new Owner($this->db, []);
            $owners = $owner->getAll();
            return json_encode($owners);
        } catch (Exception $e) {
            error_log('Error in OwnersController::index: ' . $e->getMessage());
            return json_encode(['error' => 'An error occurred while processing your request.']);
        }
    }

    /**
     * View action: Show a specific owner.
     * @param int $id The owner ID.
     * @return string
     */
    public function view(int $id): string
    {
        $this->before('view');
        try {
            $owner = new Owner($this->db, []);
            $result = $owner->find($id);
            if (!$result) {
                return json_encode(['error' => 'Owner not found.']);
            }
            return json_encode($result);
        } catch (Exception $e) {
            error_log('Error in OwnersController::view: ' . $e->getMessage());
            return json_encode(['error' => 'An error occurred while processing your request.']);
        }
    }

    /**
     * Create action: Create a new owner.
     * @param array $data The owner data.
     * @return string
     */
    public function create(array $data): string
    {
        $this->before('create');
        try {
            $owner = new Owner($this->db, []);
            $result = $owner->insert($data);
            if (!$result) {
                return json_encode(['error' => 'Error creating owner.']);
            }
            return json_encode(['id' => $result]);
        } catch (Exception $e) {
            error_log('Error in OwnersController::create: ' . $e->getMessage());
            return json_encode(['error' => 'An error occurred while processing your request.']);
        }
    }

    /**
     * Edit action: Update an existing owner.
     * @param int $id The owner ID.
     * @param array $data The owner data.
     * @return string
     */
    public function edit(int $id, array $data): string
    {
        $this->before('edit');
        try {
            $owner = new Owner($this->db, []);
            $result = $owner->update($id, $data);
            if ($result === -1) {
                return json_encode(['error' => 'Error updating owner.']);
            }
            return json_encode(['message' => 'Owner updated successfully.']);
        } catch (Exception $e) {
            error_log('Error in OwnersController::edit: ' . $e->getMessage());
            return json_encode(['error' => 'An error occurred while processing your request.']);
        }
    }

    /**
     * Delete action: Delete an owner.
     * @param int $id The owner ID.
     * @return string
     */
    public function delete(int $id): string
    {
        $this->before('delete');
        try {
            $owner = new Owner($this->db, []);
            $result = $owner->remove($id);
            if ($result === -1) {
                return json_encode(['error' => 'Error deleting owner.']);
            }
            return json_encode(['message' => 'Owner deleted successfully.']);
        } catch (Exception $e) {
            error_log('Error in OwnersController::delete: ' . $e->getMessage());
            return json_encode(['error' => 'An error occurred while processing your request.']);
        }
    }
}
