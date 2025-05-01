<?php
// filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\app\Controllers\OwnerController.php
<?php

namespace App\Controllers;

use App\Controller;
use App\Models\Owner;
use App\Helpers\SecurityHelper;

class OwnerController extends Controller {

    private Owner $ownerModel;

    public function __construct() {
        parent::__construct();
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/login');
        }
        $this->ownerModel = new Owner($this->db);
    }

    /**
     * Display a list of owners.
     */
    public function index(): void {
        $owners = $this->ownerModel->findAll();
        $this->view->output('owners/list.html', [
            'pageTitle' => 'Owners',
            'activeNav' => 'owners',
            'owners' => $owners
        ]);
    }

    /**
     * Show the form for creating a new owner.
     */
    public function create(): void {
        $this->view->output('owners/create.html', [
            'pageTitle' => 'Create New Owner',
            'activeNav' => 'owners',
            'owner' => [], // Empty for form partial
            'formAction' => '/owners/store'
        ]);
    }

    /**
     * Store a newly created owner.
     */
    public function store(): void {
        // CSRF Check
        if (!SecurityHelper::validateToken($_POST[SecurityHelper::getFormInputName()] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid request. Please try again.';
            $this->redirect('/owners/create');
            return;
        }

        // Basic Validation
        $data = $_POST;
        if (empty($data['owner_name'])) {
            $_SESSION['flash_error'] = 'Owner Name is required.';
            $_SESSION['form_data'] = $data;
            $this->redirect('/owners/create');
            return;
        }
        if ($this->ownerModel->ownerNameExists($data['owner_name'])) {
             $_SESSION['flash_error'] = 'Owner Name already exists.';
             $_SESSION['form_data'] = $data;
             $this->redirect('/owners/create');
             return;
        }

        if ($this->ownerModel->create($data)) {
            $_SESSION['flash_success'] = 'Owner created successfully.';
            unset($_SESSION['form_data']);
            $this->redirect('/owners');
        } else {
            $_SESSION['flash_error'] = 'Failed to create owner.';
            $_SESSION['form_data'] = $data;
            $this->redirect('/owners/create');
        }
    }

    /**
     * Display the specified owner (optional view).
     */
    public function view(int $id): void {
        $owner = $this->ownerModel->findById($id);
        if (!$owner) {
            $_SESSION['flash_error'] = 'Owner not found.';
            $this->redirect('/owners');
            return;
        }

        $this->view->output('owners/view.html', [
            'pageTitle' => 'View Owner: ' . htmlspecialchars($owner['owner_name']),
            'activeNav' => 'owners',
            'owner' => $owner
        ]);
    }

    /**
     * Show the form for editing the specified owner.
     */
    public function edit(int $id): void {
        $owner = $this->ownerModel->findById($id);
        if (!$owner) {
            $_SESSION['flash_error'] = 'Owner not found.';
            $this->redirect('/owners');
            return;
        }

        $formData = $_SESSION['form_data'] ?? $owner;
        unset($_SESSION['form_data']);

        $this->view->output('owners/edit.html', [
            'pageTitle' => 'Edit Owner: ' . htmlspecialchars($owner['owner_name']),
            'activeNav' => 'owners',
            'owner' => $formData,
            'formAction' => '/owners/update/' . $id
        ]);
    }

    /**
     * Update the specified owner.
     */
    public function update(int $id): void {
        // CSRF Check
        if (!SecurityHelper::validateToken($_POST[SecurityHelper::getFormInputName()] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid request. Please try again.';
            $this->redirect('/owners/edit/' . $id);
            return;
        }

        $owner = $this->ownerModel->findById($id);
        if (!$owner) {
            $_SESSION['flash_error'] = 'Owner not found.';
            $this->redirect('/owners');
            return;
        }

        // Basic Validation
        $data = $_POST;
        if (empty($data['owner_name'])) {
            $_SESSION['flash_error'] = 'Owner Name is required.';
            $_SESSION['form_data'] = $data;
            $this->redirect('/owners/edit/' . $id);
            return;
        }
         if ($this->ownerModel->ownerNameExists($data['owner_name'], $id)) {
             $_SESSION['flash_error'] = 'Owner Name already exists.';
             $_SESSION['form_data'] = $data;
             $this->redirect('/owners/edit/' . $id);
             return;
        }

        if ($this->ownerModel->update($id, $data) >= 0) {
            $_SESSION['flash_success'] = 'Owner updated successfully.';
            unset($_SESSION['form_data']);
            $this->redirect('/owners/view/' . $id); // Or redirect('/owners');
        } else {
            $_SESSION['flash_error'] = 'Failed to update owner.';
            $_SESSION['form_data'] = $data;
            $this->redirect('/owners/edit/' . $id);
        }
    }

    /**
     * Remove the specified owner.
     */
    public function delete(int $id): void {
        // CSRF Check
        if (!SecurityHelper::validateToken($_POST[SecurityHelper::getFormInputName()] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid request. Please try again.';
            $this->redirect('/owners');
            return;
        }

        $owner = $this->ownerModel->findById($id);
        if (!$owner) {
            $_SESSION['flash_error'] = 'Owner not found.';
            $this->redirect('/owners');
            return;
        }

        if ($this->ownerModel->delete($id) > 0) {
            $_SESSION['flash_success'] = 'Owner deleted successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete owner. Check if it is linked to projects (though links should become NULL).';
        }
        $this->redirect('/owners');
    }

    // Placeholder for chart action if needed later
    public function chart(): void {
         $this->view->output('owners/chart.html', [
             'pageTitle' => 'Owner Chart (Placeholder)',
             'activeNav' => 'owners'
         ]);
    }
}