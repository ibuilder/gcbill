<?php

namespace App\Controllers;

use App\Database;
use App\Models\Project;
use App\Models\Owner; // Need Owner model to populate dropdown
use App\Helpers\SecurityHelper;
// Use ValidationHelper later if created

class ProjectController extends BaseController
{
    protected string $controllerName = 'Project'; // For permissions

    private Project $projectModel;
    private Owner $ownerModel;

    public function __construct(Database $db, array $config = [])
    {
        parent::__construct($db, $config);
        // Auth check is handled by BaseController or requirePermission()
        $this->projectModel = new Project($this->db);
        $this->ownerModel = new Owner($this->db); // Instantiate Owner model
    }

    /**
     * Display a list of projects.
     */
    public function index(): void
    {
        $this->actionName = 'index';
        $this->requirePermission();

        $projects = $this->projectModel->findAllWithOwners(); // Get owner name too
        $this->render('project/list', [ // Use .php
            'pageTitle' => 'Projects',
            'activeNav' => 'projects',
            'projects' => $projects
        ]);
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(): void
    {
        $this->actionName = 'create';
        $this->requirePermission();

        $owners = $this->ownerModel->findAllSimple(); // Get owners for dropdown
        $this->render('project/create', [ // Use .php
            'pageTitle' => 'Create New Project',
            'activeNav' => 'projects',
            'owners' => $owners,
            'project' => $_SESSION['form_data'] ?? [], // Repopulate form
            'errors' => $_SESSION['errors'] ?? [],
            'formAction' => '/projects/store' // Action for the form
        ]);
        unset($_SESSION['form_data'], $_SESSION['errors']);
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(): void
    {
        $this->actionName = 'store'; // Map to 'create' permission
        $this->requirePermission();

        if (!$this->checkCsrf('/projects/create')) return;

        $data = $_POST['project'] ?? []; // Assuming form fields like project[project_name]
        $errors = [];

        // --- Validation ---
        if (empty($data['project_number'])) $errors['project_number'] = 'Project Number is required.';
        elseif ($this->projectModel->projectNumberExists($data['project_number'])) $errors['project_number'] = 'Project Number already exists.';
        if (empty($data['project_name'])) $errors['project_name'] = 'Project Name is required.';
        if (empty($data['owner_id'])) $errors['owner_id'] = 'Owner is required.';
        // Add more validation (dates, amounts, etc.)
        // --- End Validation ---

        if (!empty($errors)) {
            $_SESSION['form_data'] = $data;
            $_SESSION['errors'] = $errors;
            $this->setFlashMessage('error', 'Please correct the errors below.');
            $this->redirect('/projects/create');
            return;
        }

        // Prepare data (ensure types, nulls)
        $projectData = $this->prepareProjectData($data);


        if ($this->projectModel->create($projectData)) {
            $this->setFlashMessage('success', 'Project created successfully.');
            $this->redirect('/projects');
        } else {
            $_SESSION['form_data'] = $data;
            $this->setFlashMessage('error', 'Failed to create project.');
            $this->redirect('/projects/create');
        }
    }

    /**
     * Display the specified project.
     */
    public function view(int $id): void
    {
        $this->actionName = 'view';
        $this->requirePermission();

        $project = $this->projectModel->findByIdWithOwners($id); // Get owner name
        if (!$project) {
            $this->setFlashMessage('error', 'Project not found.');
            $this->redirect('/projects');
            return;
        }

        // Potentially load related data summary (SOV total, Billings count) here if needed for view

        $this->render('project/view', [ // Use .php
            'pageTitle' => 'View Project: ' . htmlspecialchars($project['project_name']),
            'activeNav' => 'projects',
            'project' => $project
            // Pass related summary data if loaded
        ]);
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(int $id): void
    {
        $this->actionName = 'edit';
        $this->requirePermission();

        $project = $this->projectModel->findById($id);
        if (!$project) {
            $this->setFlashMessage('error', 'Project not found.');
            $this->redirect('/projects');
            return;
        }

        $owners = $this->ownerModel->findAllSimple(); // Get owners for dropdown

        // Use session data if validation failed on update attempt
        $formData = $_SESSION['form_data'] ?? $project;
        unset($_SESSION['form_data']);
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);

        $this->render('project/edit', [ // Use .php
            'pageTitle' => 'Edit Project: ' . htmlspecialchars($project['project_name']),
            'activeNav' => 'projects',
            'project' => $formData, // Use potentially repopulated data
            'owners' => $owners,
            'errors' => $errors,
            'formAction' => '/projects/update/' . $id // Action for the form
        ]);
    }

    /**
     * Update the specified project in storage.
     */
    public function update(int $id): void
    {
        $this->actionName = 'update'; // Map to 'edit' permission
        $this->requirePermission();

        if (!$this->checkCsrf('/projects/edit/' . $id)) return;

        $project = $this->projectModel->findById($id);
        if (!$project) {
            $this->setFlashMessage('error', 'Project not found.');
            $this->redirect('/projects');
            return;
        }

        $data = $_POST['project'] ?? [];
        $errors = [];

        // --- Validation ---
        if (empty($data['project_number'])) $errors['project_number'] = 'Project Number is required.';
        elseif ($this->projectModel->projectNumberExists($data['project_number'], $id)) $errors['project_number'] = 'Project Number already exists.';
        if (empty($data['project_name'])) $errors['project_name'] = 'Project Name is required.';
        if (empty($data['owner_id'])) $errors['owner_id'] = 'Owner is required.';
        // Add more validation
        // --- End Validation ---

        if (!empty($errors)) {
            $_SESSION['form_data'] = $data;
            $_SESSION['errors'] = $errors;
            $this->setFlashMessage('error', 'Please correct the errors below.');
            $this->redirect('/projects/edit/' . $id);
            return;
        }

        // Prepare data (ensure types, nulls)
        $projectData = $this->prepareProjectData($data);

        if ($this->projectModel->update($id, $projectData) >= 0) { // Check >= 0
            $this->setFlashMessage('success', 'Project updated successfully.');
            $this->redirect('/projects/view/' . $id); // Redirect to view
        } else {
            $_SESSION['form_data'] = $data;
            $this->setFlashMessage('error', 'Failed to update project.');
            $this->redirect('/projects/edit/' . $id);
        }
    }

    /**
     * Remove the specified project from storage.
     * Assumes POST request for deletion for CSRF protection.
     */
    public function delete(int $id): void
    {
        $this->actionName = 'delete';
        $this->requirePermission();

        // Use POST for delete, check CSRF
        if (!$this->checkCsrf('/projects')) return; // Redirect to list on CSRF fail

        $project = $this->projectModel->findById($id);
        if (!$project) {
            $this->setFlashMessage('error', 'Project not found.');
            $this->redirect('/projects');
            return;
        }

        // Optional: Check for related records before deleting
        // if ($this->projectModel->hasRelatedBillings($id) || $this->projectModel->hasRelatedSov($id)) {
        //     $this->setFlashMessage('error', 'Cannot delete project with existing billings or SOV items.');
        //     $this->redirect('/projects');
        //     return;
        // }

        if ($this->projectModel->delete($id)) {
            $this->setFlashMessage('success', 'Project deleted successfully.');
        } else {
            $this->setFlashMessage('error', 'Failed to delete project.');
        }
        $this->redirect('/projects');
    }

    /**
     * Helper to prepare project data for DB insert/update.
     */
    private function prepareProjectData(array $data): array
    {
        // Ensure correct types and handle empty strings for nullable fields
        return [
            'project_number' => trim($data['project_number'] ?? ''),
            'project_name' => trim($data['project_name'] ?? ''),
            'owner_id' => isset($data['owner_id']) && $data['owner_id'] !== '' ? (int)$data['owner_id'] : null,
            'address_line1' => trim($data['address_line1'] ?? '') ?: null,
            'address_line2' => trim($data['address_line2'] ?? '') ?: null,
            'city' => trim($data['city'] ?? '') ?: null,
            'state' => trim($data['state'] ?? '') ?: null,
            'zip_code' => trim($data['zip_code'] ?? '') ?: null,
            'status' => trim($data['status'] ?? 'active'), // Default status
            'start_date' => !empty($data['start_date']) ? date('Y-m-d', strtotime($data['start_date'])) : null,
            'completion_date' => !empty($data['completion_date']) ? date('Y-m-d', strtotime($data['completion_date'])) : null,
            'contract_amount' => isset($data['contract_amount']) && $data['contract_amount'] !== '' ? (float)$data['contract_amount'] : null,
            'gmp_amount' => isset($data['gmp_amount']) && $data['gmp_amount'] !== '' ? (float)$data['gmp_amount'] : null,
            'gc_fee_percentage' => isset($data['gc_fee_percentage']) && $data['gc_fee_percentage'] !== '' ? (float)$data['gc_fee_percentage'] : null,
            'retainage_percentage' => isset($data['retainage_percentage']) && $data['retainage_percentage'] !== '' ? (float)$data['retainage_percentage'] : null,
            // Add other fields as needed
        ];
    }
}