<?php

namespace App\Controllers; // Correct namespace

use App\Database;
use App\Models\Owner;
use App\Helpers\SecurityHelper;

class OwnerController extends BaseController
{
    protected string $controllerName = 'Owner'; // For permissions

    private Owner $ownerModel;

    public function __construct(Database $db, array $config = [])
    {
        parent::__construct($db, $config);
        // Auth check handled by BaseController or requirePermission()
        $this->ownerModel = new Owner($this->db);
    }

    /**
     * Display a list of owners.
     */
    public function index(): void
    {
        $this->actionName = 'index';
        $this->requirePermission();

        $owners = $this->ownerModel->findAll();
        $this->render('owners/list', [ // Use .php
            'pageTitle' => 'Owners',
            'activeNav' => 'owners',
            'owners' => $owners
        ]);
    }

    /**
     * Show the form for creating a new owner.
     */
    public function create(): void
    {
        $this->actionName = 'create';
        $this->requirePermission();

        $this->render('owners/create', [ // Use .php
            'pageTitle' => 'Create New Owner',
            'activeNav' => 'owners',
            'owner' => $_SESSION['form_data'] ?? [], // Repopulate form
            'errors' => $_SESSION['errors'] ?? [],
            'formAction' => '/owners/store'
        ]);
        unset($_SESSION['form_data'], $_SESSION['errors']);
    }

    /**
     * Store a newly created owner.
     */
    public function store(): void
    {
        $this->actionName = 'store'; // Map to 'create' permission
        $this->requirePermission();

        if (!$this->checkCsrf('/owners/create')) return;

        $data = $_POST['owner'] ?? []; // Assuming form fields like owner[owner_name]
        $errors = [];

        // --- Validation ---
        if (empty($data['owner_name'])) {
            $errors['owner_name'] = 'Owner Name is required.';
        } elseif ($this->ownerModel->ownerNameExists($data['owner_name'])) {
            $errors['owner_name'] = 'Owner Name already exists.';
        }
        // Add validation for other fields (email, phone, address)
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
             $errors['email'] = 'Invalid Email format.';
        }
        // --- End Validation ---

        if (!empty($errors)) {
            $_SESSION['form_data'] = $data;
            $_SESSION['errors'] = $errors;
            $this->setFlashMessage('error', 'Please correct the errors below.');
            $this->redirect('/owners/create');
            return;
        }

        // Prepare data (handle empty strings for nullable fields)
        $ownerData = $this->prepareOwnerData($data);

        if ($this->ownerModel->create($ownerData)) {
            $this->setFlashMessage('success', 'Owner created successfully.');
            $this->redirect('/owners');
        } else {
            $_SESSION['form_data'] = $data;
            $this->setFlashMessage('error', 'Failed to create owner.');
            $this->redirect('/owners/create');
        }
    }

    /**
     * Display the specified owner.
     */
    public function view(int $id): void
    {
        $this->actionName = 'view';
        $this->requirePermission();

        $owner = $this->ownerModel->findById($id);
        if (!$owner) {
            $this->setFlashMessage('error', 'Owner not found.');
            $this->redirect('/owners');
            return;
        }

        $this->render('owners/view', [ // Use .php
            'pageTitle' => 'View Owner: ' . htmlspecialchars($owner['owner_name']),
            'activeNav' => 'owners',
            'owner' => $owner
        ]);
    }

    /**
     * Show the form for editing the specified owner.
     */
    public function edit(int $id): void
    {
        $this->actionName = 'edit';
        $this->requirePermission();

        $owner = $this->ownerModel->findById($id);
        if (!$owner) {
            $this->setFlashMessage('error', 'Owner not found.');
            $this->redirect('/owners');
            return;
        }

        $formData = $_SESSION['form_data'] ?? $owner;
        unset($_SESSION['form_data']);
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);

        $this->render('owners/edit', [ // Use .php
            'pageTitle' => 'Edit Owner: ' . htmlspecialchars($owner['owner_name']),
            'activeNav' => 'owners',
            'owner' => $formData,
            'errors' => $errors,
            'formAction' => '/owners/update/' . $id
        ]);
    }

    /**
     * Update the specified owner.
     */
    public function update(int $id): void
    {
        $this->actionName = 'update'; // Map to 'edit' permission
        $this->requirePermission();

        if (!$this->checkCsrf('/owners/edit/' . $id)) return;

        $owner = $this->ownerModel->findById($id);
        if (!$owner) {
            $this->setFlashMessage('error', 'Owner not found.');
            $this->redirect('/owners');
            return;
        }

        $data = $_POST['owner'] ?? [];
        $errors = [];

        // --- Validation ---
        if (empty($data['owner_name'])) {
            $errors['owner_name'] = 'Owner Name is required.';
        } elseif ($this->ownerModel->ownerNameExists($data['owner_name'], $id)) {
            $errors['owner_name'] = 'Owner Name already exists.';
        }
        // Add validation for other fields
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
             $errors['email'] = 'Invalid Email format.';
        }
        // --- End Validation ---

        if (!empty($errors)) {
            $_SESSION['form_data'] = $data;
            $_SESSION['errors'] = $errors;
            $this->setFlashMessage('error', 'Please correct the errors below.');
            $this->redirect('/owners/edit/' . $id);
            return;
        }

        // Prepare data
        $ownerData = $this->prepareOwnerData($data);

        if ($this->ownerModel->update($id, $ownerData) >= 0) { // Allow 0 rows affected
            $this->setFlashMessage('success', 'Owner updated successfully.');
            $this->redirect('/owners/view/' . $id); // Or redirect('/owners');
        } else {
            $_SESSION['form_data'] = $data;
            $this->setFlashMessage('error', 'Failed to update owner.');
            $this->redirect('/owners/edit/' . $id);
        }
    }

    /**
     * Remove the specified owner.
     */
    public function delete(int $id): void
    {
        $this->actionName = 'delete';
        $this->requirePermission();

        // Use POST for delete, check CSRF
        if (!$this->checkCsrf('/owners')) return; // Redirect to list on CSRF fail

        $owner = $this->ownerModel->findById($id);
        if (!$owner) {
            $this->setFlashMessage('error', 'Owner not found.');
            $this->redirect('/owners');
            return;
        }

        // Optional: Check if owner is linked to projects before deleting
        // if ($this->ownerModel->isLinkedToProjects($id)) {
        //     $this->setFlashMessage('error', 'Cannot delete owner linked to existing projects.');
        //     $this->redirect('/owners');
        //     return;
        // }

        if ($this->ownerModel->delete($id)) {
            $this->setFlashMessage('success', 'Owner deleted successfully.');
        } else {
            $this->setFlashMessage('error', 'Failed to delete owner.');
        }
        $this->redirect('/owners');
    }

    /**
     * Helper to prepare owner data for DB insert/update.
     */
    private function prepareOwnerData(array $data): array
    {
        // Ensure correct types and handle empty strings for nullable fields
        return [
            'owner_name' => trim($data['owner_name'] ?? ''),
            'contact_person' => trim($data['contact_person'] ?? '') ?: null,
            'email' => trim($data['email'] ?? '') ?: null,
            'phone' => trim($data['phone'] ?? '') ?: null,
            'address_line1' => trim($data['address_line1'] ?? '') ?: null,
            'address_line2' => trim($data['address_line2'] ?? '') ?: null,
            'city' => trim($data['city'] ?? '') ?: null,
            'state' => trim($data['state'] ?? '') ?: null,
            'zip_code' => trim($data['zip_code'] ?? '') ?: null,
            // Add other fields as needed
        ];
    }


    // Placeholder for chart action if needed later
    public function chart(): void
    {
        $this->actionName = 'chart'; // Define permission if needed
        $this->requirePermission();

        // TODO: Fetch data needed for the chart
        $this->render('owners/chart', [ // Use .php
            'pageTitle' => 'Owner Chart (Placeholder)',
            'activeNav' => 'owners'
            // Pass chart data
        ]);
    }
}