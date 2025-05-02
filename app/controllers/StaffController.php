<?php

namespace App\Controllers;

use App\Database;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\User; // Need User model for linking
use App\Helpers\SecurityHelper;
// Auth is likely handled by BaseController now

class StaffController extends BaseController
{
    protected string $controllerName = 'Staff'; // For permissions

    private Staff $staffModel;
    private StaffPosition $positionModel;
    private User $userModel;

    public function __construct(Database $db, array $config = [])
    {
        parent::__construct($db, $config);
        $this->staffModel = new Staff($this->db);
        $this->positionModel = new StaffPosition($this->db);
        $this->userModel = new User($this->db); // For user linking dropdown
    }

    // before() method is likely handled by BaseController, remove if not needed here specifically

    // --- Staff Member CRUD ---

    public function index(): void
    {
        $this->actionName = 'index';
        $this->requirePermission(); // Check permission

        $staffList = $this->staffModel->findAllWithDetails(); // Assuming a method to get position title etc.
        $this->render('staff/list', [ // Use .php
            'pageTitle' => 'Staff Members',
            'activeNav' => 'staff',
            'staffList' => $staffList
        ]);
    }

    public function create(): void
    {
        $this->actionName = 'create';
        $this->requirePermission();

        $positions = $this->positionModel->findAll();
        $users = $this->userModel->findAllUnlinked(); // Method needed in User model to find users not linked to staff
        $this->render('staff/create', [ // Use .php
            'pageTitle' => 'Add New Staff Member',
            'activeNav' => 'staff',
            'positions' => $positions,
            'users' => $users,
            'staff' => $_SESSION['form_data'] ?? [], // Repopulate form
            'errors' => $_SESSION['errors'] ?? [],
            'formAction' => '/staff/store'
        ]);
        unset($_SESSION['form_data'], $_SESSION['errors']);
    }

    public function store(): void
    {
        $this->actionName = 'store'; // Map to 'create' permission
        $this->requirePermission();

        if (!$this->checkCsrf('/staff/create')) return;

        $data = $_POST['staff'] ?? []; // Assuming form fields like staff[first_name]
        $errors = [];

        // --- Validation ---
        if (empty($data['first_name'])) $errors['first_name'] = 'First Name is required.';
        if (empty($data['last_name'])) $errors['last_name'] = 'Last Name is required.';
        if (empty($data['position_id'])) $errors['position_id'] = 'Position is required.';
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid Email format.';
        if (!empty($data['email']) && $this->staffModel->emailExists($data['email'])) $errors['email'] = 'Staff email already exists.';
        if (!empty($data['user_id']) && $this->staffModel->userIdLinked((int)$data['user_id'])) $errors['user_id'] = 'Selected user is already linked to another staff member.';
        // --- End Validation ---

        if (!empty($errors)) {
            $_SESSION['form_data'] = $data;
            $_SESSION['errors'] = $errors;
            $this->setFlashMessage('error', 'Please correct the errors below.');
            $this->redirect('/staff/create');
            return;
        }

        if ($this->staffModel->create($data)) {
            $this->setFlashMessage('success', 'Staff member created successfully.');
            $this->redirect('/staff');
        } else {
            $_SESSION['form_data'] = $data;
            $this->setFlashMessage('error', 'Failed to create staff member.');
            $this->redirect('/staff/create');
        }
    }

    public function view(int $id): void
    {
        $this->actionName = 'view';
        $this->requirePermission();

        $staff = $this->staffModel->findByIdWithDetails($id); // Get details like position title
        if (!$staff) {
            $this->setFlashMessage('error', 'Staff member not found.');
            $this->redirect('/staff');
            return;
        }
        $this->render('staff/view', [ // Use .php
            'pageTitle' => 'View Staff: ' . htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']),
            'activeNav' => 'staff',
            'staff' => $staff
        ]);
    }

    public function edit(int $id): void
    {
        $this->actionName = 'edit';
        $this->requirePermission();

        $staff = $this->staffModel->findById($id);
        if (!$staff) {
            $this->setFlashMessage('error', 'Staff member not found.');
            $this->redirect('/staff');
            return;
        }
        $positions = $this->positionModel->findAll();
        // Get users not linked OR the currently linked user
        $users = $this->userModel->findAllUnlinkedOrCurrent($staff['user_id'] ?? null);

        $formData = $_SESSION['form_data'] ?? $staff;
        unset($_SESSION['form_data']);
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);

        $this->render('staff/edit', [ // Use .php
            'pageTitle' => 'Edit Staff Member',
            'activeNav' => 'staff',
            'staff' => $formData,
            'positions' => $positions,
            'users' => $users,
            'errors' => $errors,
            'formAction' => '/staff/update/' . $id
        ]);
    }

    public function update(int $id): void
    {
        $this->actionName = 'update'; // Map to 'edit' permission
        $this->requirePermission();

        if (!$this->checkCsrf('/staff/edit/' . $id)) return;

        $staff = $this->staffModel->findById($id); // Check exists
        if (!$staff) {
            $this->setFlashMessage('error', 'Staff member not found.');
            $this->redirect('/staff');
            return;
        }

        $data = $_POST['staff'] ?? [];
        $errors = [];

        // --- Validation ---
        if (empty($data['first_name'])) $errors['first_name'] = 'First Name is required.';
        if (empty($data['last_name'])) $errors['last_name'] = 'Last Name is required.';
        if (empty($data['position_id'])) $errors['position_id'] = 'Position is required.';
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid Email format.';
        if (!empty($data['email']) && $this->staffModel->emailExists($data['email'], $id)) $errors['email'] = 'Staff email already exists.';
        if (!empty($data['user_id']) && $this->staffModel->userIdLinked((int)$data['user_id'], $id)) $errors['user_id'] = 'Selected user is already linked to another staff member.';
        // --- End Validation ---

        if (!empty($errors)) {
            $_SESSION['form_data'] = $data;
            $_SESSION['errors'] = $errors;
            $this->setFlashMessage('error', 'Please correct the errors below.');
            $this->redirect('/staff/edit/' . $id);
            return;
        }

        // Ensure user_id is null if empty string is submitted
        if (isset($data['user_id']) && $data['user_id'] === '') {
            $data['user_id'] = null;
        }


        if ($this->staffModel->update($id, $data) >= 0) { // Allow 0 rows affected
            $this->setFlashMessage('success', 'Staff member updated successfully.');
            $this->redirect('/staff/view/' . $id);
        } else {
            $_SESSION['form_data'] = $data;
            $this->setFlashMessage('error', 'Failed to update staff member.');
            $this->redirect('/staff/edit/' . $id);
        }
    }

    public function delete(int $id): void
    {
        $this->actionName = 'delete';
        $this->requirePermission();

        // Use POST for delete, check CSRF
        if (!$this->checkCsrf('/staff')) return; // Redirect to list on CSRF fail

        $staff = $this->staffModel->findById($id); // Check exists
        if (!$staff) {
            $this->setFlashMessage('error', 'Staff member not found.');
            $this->redirect('/staff');
            return;
        }

        if ($this->staffModel->delete($id)) {
            $this->setFlashMessage('success', 'Staff member deleted successfully.');
        } else {
            $this->setFlashMessage('error', 'Failed to delete staff member. Check related records (e.g., time entries).');
        }
        $this->redirect('/staff');
    }

    // --- Staff Position CRUD ---

    public function positions(): void
    {
        $this->actionName = 'positions'; // Define permission if needed
        $this->requirePermission();

        $positions = $this->positionModel->findAll();
        $this->render('staff/positions_list', [ // Use .php
            'pageTitle' => 'Staff Positions',
            'activeNav' => 'staff-positions', // Separate nav item?
            'positions' => $positions
        ]);
    }

    public function createPosition(): void
    {
        $this->actionName = 'createPosition'; // Define permission
        $this->requirePermission();

        $this->render('staff/positions_create', [ // Use .php
            'pageTitle' => 'Add New Staff Position',
            'activeNav' => 'staff-positions',
            'position' => $_SESSION['form_data'] ?? [],
            'errors' => $_SESSION['errors'] ?? [],
            'formAction' => '/staff/positions/store'
        ]);
         unset($_SESSION['form_data'], $_SESSION['errors']);
    }

    public function storePosition(): void
    {
        $this->actionName = 'storePosition'; // Map to createPosition permission
        $this->requirePermission();

        if (!$this->checkCsrf('/staff/positions/create')) return;

        $data = $_POST['position'] ?? [];
        $errors = [];

        if (empty($data['position_title'])) {
            $errors['position_title'] = 'Position Title is required.';
        } elseif ($this->positionModel->positionTitleExists($data['position_title'])) {
            $errors['position_title'] = 'Position Title already exists.';
        }

        if (!empty($errors)) {
            $_SESSION['form_data'] = $data;
            $_SESSION['errors'] = $errors;
            $this->setFlashMessage('error', 'Please correct the errors below.');
            $this->redirect('/staff/positions/create');
            return;
        }

        if ($this->positionModel->create($data)) {
            $this->setFlashMessage('success', 'Staff position created successfully.');
            $this->redirect('/staff/positions');
        } else {
            $_SESSION['form_data'] = $data;
            $this->setFlashMessage('error', 'Failed to create staff position.');
            $this->redirect('/staff/positions/create');
        }
    }

    public function editPosition(int $id): void
    {
        $this->actionName = 'editPosition'; // Define permission
        $this->requirePermission();

        $position = $this->positionModel->findById($id);
        if (!$position) {
            $this->setFlashMessage('error', 'Staff position not found.');
            $this->redirect('/staff/positions');
            return;
        }
        $formData = $_SESSION['form_data'] ?? $position;
        unset($_SESSION['form_data']);
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);

        $this->render('staff/positions_edit', [ // Use .php
            'pageTitle' => 'Edit Staff Position',
            'activeNav' => 'staff-positions',
            'position' => $formData,
            'errors' => $errors,
            'formAction' => '/staff/positions/update/' . $id
        ]);
    }

    public function updatePosition(int $id): void
    {
        $this->actionName = 'updatePosition'; // Map to editPosition permission
        $this->requirePermission();

        if (!$this->checkCsrf('/staff/positions/edit/' . $id)) return;

        $position = $this->positionModel->findById($id);
        if (!$position) {
            $this->setFlashMessage('error', 'Staff position not found.');
            $this->redirect('/staff/positions');
            return;
        }
        $data = $_POST['position'] ?? [];
        $errors = [];

        if (empty($data['position_title'])) {
            $errors['position_title'] = 'Position Title is required.';
        } elseif ($this->positionModel->positionTitleExists($data['position_title'], $id)) {
            $errors['position_title'] = 'Position Title already exists.';
        }

        if (!empty($errors)) {
            $_SESSION['form_data'] = $data;
            $_SESSION['errors'] = $errors;
            $this->setFlashMessage('error', 'Please correct the errors below.');
            $this->redirect('/staff/positions/edit/' . $id);
            return;
        }

        if ($this->positionModel->update($id, $data) >= 0) { // Allow 0 rows affected
            $this->setFlashMessage('success', 'Staff position updated successfully.');
            $this->redirect('/staff/positions');
        } else {
            $_SESSION['form_data'] = $data;
            $this->setFlashMessage('error', 'Failed to update staff position.');
            $this->redirect('/staff/positions/edit/' . $id);
        }
    }

    public function deletePosition(int $id): void
    {
        $this->actionName = 'deletePosition'; // Define permission
        $this->requirePermission();

        // Use POST for delete, check CSRF
        if (!$this->checkCsrf('/staff/positions')) return;

        $position = $this->positionModel->findById($id);
        if (!$position) {
            $this->setFlashMessage('error', 'Staff position not found.');
            $this->redirect('/staff/positions');
            return;
        }

        // Check if position is in use before deleting
        if ($this->positionModel->isPositionInUse($id)) {
             $this->setFlashMessage('error', 'Cannot delete position as it is currently assigned to staff members.');
             $this->redirect('/staff/positions');
             return;
        }


        if ($this->positionModel->delete($id)) {
            $this->setFlashMessage('success', 'Staff position deleted successfully.');
        } else {
            $this->setFlashMessage('error', 'Failed to delete staff position.');
        }
        $this->redirect('/staff/positions');
    }

    // Placeholder for chart
    public function chart(): void
    {
        $this->actionName = 'chart'; // Define permission if needed
        $this->requirePermission();

        // TODO: Fetch data for the org chart
        $staffDataForChart = $this->staffModel->findAllForOrgChart(); // Example method

        $this->render('staff/chart', [ // Use .php
            'pageTitle' => 'Staff Organization Chart',
            'activeNav' => 'staff-chart', // Separate nav item?
            'staffData' => $staffDataForChart
        ]);
    }
}