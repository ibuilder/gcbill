<?php
// filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\app\Controllers\StaffController.php
<?php

namespace App\Controllers;

use App\Controller;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\User; // Need User model for linking
use App\Helpers\SecurityHelper;
use App\Libraries\Auth;

class StaffController extends Controller {

    private Staff $staffModel;
    private StaffPosition $positionModel;
    private User $userModel;

    public function __construct() {
        
        parent::__construct();       
        $this->staffModel = new Staff($this->db);
        $this->positionModel = new StaffPosition($this->db);
        $this->userModel = new User($this->db); // For user linking dropdown
        
    }
    
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

    // --- Staff Member CRUD ---

    public function index(): void {
        $staffList = $this->staffModel->findAll();
        $this->view->output('staff/list.html', [
            'pageTitle' => 'Staff Members',
            'user' => $_SESSION['user'],
            'activeNav' => 'staff',
            'staffList' => $staffList
        ]);
    }

    public function create(): void {
        $positions = $this->positionModel->findAll();
        $users = $this->userModel->findAllSimple(); // Method needed in User model
        $this->view->output('staff/create.html', [
            'user' => $_SESSION['user'],
            'pageTitle' => 'Add New Staff Member',
            'activeNav' => 'staff',
            'positions' => $positions,
            'users' => $users,
            'staff' => [],
            'formAction' => '/staff/store'
        ]);
    }

    public function store(): void {
        if (!SecurityHelper::validateToken($_POST[SecurityHelper::getFormInputName()] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid request.'; $this->redirect('/staff/create'); return;
        }

        $data = $_POST;
        // Basic Validation
        if (empty($data['first_name']) || empty($data['last_name'])) {
            $_SESSION['flash_error'] = 'First Name and Last Name are required.';
            $_SESSION['form_data'] = $data; $this->redirect('/staff/create'); return;
        }
        if (!empty($data['email']) && $this->staffModel->emailExists($data['email'])) {
             $_SESSION['flash_error'] = 'Staff email already exists.';
             $_SESSION['form_data'] = $data; $this->redirect('/staff/create'); return;
        }
         if (!empty($data['user_id']) && $this->staffModel->userIdLinked((int)$data['user_id'])) {
             $_SESSION['flash_error'] = 'Selected system user is already linked to another staff member.';
             $_SESSION['form_data'] = $data; $this->redirect('/staff/create'); return;
        }

        if ($this->staffModel->create($data)) {
            $_SESSION['flash_success'] = 'Staff member created successfully.';
            unset($_SESSION['form_data']); $this->redirect('/staff');
        } else {
            $_SESSION['flash_error'] = 'Failed to create staff member.';
            $_SESSION['form_data'] = $data; $this->redirect('/staff/create');
        }
    }

    public function view(int $id): void {
        $staff = $this->staffModel->findById($id);
        if (!$staff) {
            $_SESSION['flash_error'] = 'Staff member not found.'; $this->redirect('/staff'); return;
        }
        $this->view->output('staff/view.html', [
            'user' => $_SESSION['user'],
            'pageTitle' => 'View Staff: ' . htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']),
            'activeNav' => 'staff',
            'staff' => $staff
        ]);
    }

    public function edit(int $id): void {
        $staff = $this->staffModel->findById($id);
        if (!$staff) {
            $_SESSION['flash_error'] = 'Staff member not found.'; $this->redirect('/staff'); return;
        }
        $positions = $this->positionModel->findAll();
        $users = $this->userModel->findAllSimple(); // Method needed in User model
        $formData = $_SESSION['form_data'] ?? $staff;
        unset($_SESSION['form_data']);

        $this->view->output('staff/edit.html', [
            'user' => $_SESSION['user'],
            'pageTitle' => 'Edit Staff Member',
            'activeNav' => 'staff',
            'staff' => $formData,
            'positions' => $positions,
            'users' => $users,
            'formAction' => '/staff/update/' . $id
        ]);
    }

    public function update(int $id): void {
         if (!SecurityHelper::validateToken($_POST[SecurityHelper::getFormInputName()] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid request.'; $this->redirect('/staff/edit/' . $id); return;
        }
        $staff = $this->staffModel->findById($id); // Check exists
        if (!$staff) {
            $_SESSION['flash_error'] = 'Staff member not found.'; $this->redirect('/staff'); return;
        }

        $data = $_POST;
        // Basic Validation
        if (empty($data['first_name']) || empty($data['last_name'])) {
            $_SESSION['flash_error'] = 'First Name and Last Name are required.';
            $_SESSION['form_data'] = $data; $this->redirect('/staff/edit/' . $id); return;
        }
         if (!empty($data['email']) && $this->staffModel->emailExists($data['email'], $id)) {
             $_SESSION['flash_error'] = 'Staff email already exists.';
             $_SESSION['form_data'] = $data; $this->redirect('/staff/edit/' . $id); return;
        }
         if (!empty($data['user_id']) && $this->staffModel->userIdLinked((int)$data['user_id'], $id)) {
             $_SESSION['flash_error'] = 'Selected system user is already linked to another staff member.';
             $_SESSION['form_data'] = $data; $this->redirect('/staff/edit/' . $id); return;
        }

        if ($this->staffModel->update($id, $data) >= 0) {
            $_SESSION['flash_success'] = 'Staff member updated successfully.';
            unset($_SESSION['form_data']); $this->redirect('/staff/view/' . $id);
        } else {
            $_SESSION['flash_error'] = 'Failed to update staff member.';
            $_SESSION['form_data'] = $data; $this->redirect('/staff/edit/' . $id);
        }
    }

    public function delete(int $id): void {
         if (!SecurityHelper::validateToken($_POST[SecurityHelper::getFormInputName()] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid request.'; $this->redirect('/staff'); return;
        }
        $staff = $this->staffModel->findById($id); // Check exists
        if (!$staff) {
            $_SESSION['flash_error'] = 'Staff member not found.'; $this->redirect('/staff'); return;
        }

        if ($this->staffModel->delete($id) > 0) {
            $_SESSION['flash_success'] = 'Staff member deleted successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete staff member. Check related records.';
        }
        $this->redirect('/staff');
    }

    // --- Staff Position CRUD ---

    public function positions(): void {
        $positions = $this->positionModel->findAll();
        $this->view->output('staff/positions_list.html', [
            'user' => $_SESSION['user'],
            'pageTitle' => 'Staff Positions',
            'activeNav' => 'staff', // Keep staff nav active
            'positions' => $positions
        ]);
    }

    public function createPosition(): void {
         $this->view->output('staff/positions_create.html', [
            'user' => $_SESSION['user'],
            'pageTitle' => 'Add New Staff Position',
            'activeNav' => 'staff',
            'position' => [],
            'formAction' => '/staff/positions/store'
        ]);
    }

    public function storePosition(): void {
         if (!SecurityHelper::validateToken($_POST[SecurityHelper::getFormInputName()] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid request.'; $this->redirect('/staff/positions/create'); return;
        }
        $data = $_POST;
        if (empty($data['position_title'])) {
            $_SESSION['flash_error'] = 'Position Title is required.';
            $_SESSION['form_data'] = $data; $this->redirect('/staff/positions/create'); return;
        }
         if ($this->positionModel->positionTitleExists($data['position_title'])) {
             $_SESSION['flash_error'] = 'Position Title already exists.';
             $_SESSION['form_data'] = $data; $this->redirect('/staff/positions/create'); return;
        }

        if ($this->positionModel->create($data)) {
            $_SESSION['flash_success'] = 'Staff position created successfully.';
            unset($_SESSION['form_data']); $this->redirect('/staff/positions');
        } else {
            $_SESSION['flash_error'] = 'Failed to create staff position.';
            $_SESSION['form_data'] = $data; $this->redirect('/staff/positions/create');
        }
    }

    // Add editPosition, updatePosition, deletePosition methods similarly...
    public function editPosition(int $id): void {
         $position = $this->positionModel->findById($id);
         if (!$position) {
             $_SESSION['flash_error'] = 'Staff position not found.'; $this->redirect('/staff/positions'); return;
         }
         $formData = $_SESSION['form_data'] ?? $position;
         unset($_SESSION['form_data']);
         $this->view->output('staff/positions_edit.html', [
            'user' => $_SESSION['user'],
             'pageTitle' => 'Edit Staff Position',
             'activeNav' => 'staff',
             'position' => $formData,
             'formAction' => '/staff/positions/update/' . $id
         ]);
    }

    public function updatePosition(int $id): void {
         if (!SecurityHelper::validateToken($_POST[SecurityHelper::getFormInputName()] ?? null)) {
             $_SESSION['flash_error'] = 'Invalid request.'; $this->redirect('/staff/positions/edit/' . $id); return;
         }
         $position = $this->positionModel->findById($id);
         if (!$position) {
             $_SESSION['flash_error'] = 'Staff position not found.'; $this->redirect('/staff/positions'); return;
         }
         $data = $_POST;
         if (empty($data['position_title'])) {
             $_SESSION['flash_error'] = 'Position Title is required.';
             $_SESSION['form_data'] = $data; $this->redirect('/staff/positions/edit/' . $id); return;
         }
         if ($this->positionModel->positionTitleExists($data['position_title'], $id)) {
              $_SESSION['flash_error'] = 'Position Title already exists.';
              $_SESSION['form_data'] = $data; $this->redirect('/staff/positions/edit/' . $id); return;
         }

         if ($this->positionModel->update($id, $data) >= 0) {
             $_SESSION['flash_success'] = 'Staff position updated successfully.';
             unset($_SESSION['form_data']); $this->redirect('/staff/positions');
         } else {
             $_SESSION['flash_error'] = 'Failed to update staff position.';
             $_SESSION['form_data'] = $data; $this->redirect('/staff/positions/edit/' . $id);
         }
    }

    public function deletePosition(int $id): void {
         if (!SecurityHelper::validateToken($_POST[SecurityHelper::getFormInputName()] ?? null)) {
             $_SESSION['flash_error'] = 'Invalid request.'; $this->redirect('/staff/positions'); return;
         }
         $position = $this->positionModel->findById($id);
         if (!$position) {
             $_SESSION['flash_error'] = 'Staff position not found.'; $this->redirect('/staff/positions'); return;
         }
         if ($this->positionModel->delete($id) > 0) {
             $_SESSION['flash_success'] = 'Staff position deleted successfully.';
         } else {
             $_SESSION['flash_error'] = 'Failed to delete staff position.';
         }
         $this->redirect('/staff/positions');
    }

    // Placeholder for chart
    public function chart(): void {
         $this->view->output('staff/chart.html', [
            'user' => $_SESSION['user'],
             'pageTitle' => 'Staff Chart (Placeholder)',
             'activeNav' => 'staff'
         ]);
    }
}