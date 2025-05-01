<?php
// filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\app\Controllers\ProjectController.php
<?php

namespace App\Controllers;

use App\Controller;
use App\Models\Project;
use App\Models\Owner; // Need Owner model to populate dropdown
use App\Helpers\SecurityHelper;
// Use ValidationHelper later

class ProjectController extends Controller {

    private Project $projectModel;
    private Owner $ownerModel;

    public function __construct() {
        parent::__construct();
        if (!$this->auth->isLoggedIn()) { // Ensure user is logged in
            $this->redirect('/login');
        }
        $this->projectModel = new Project($this->db);
        $this->ownerModel = new Owner($this->db); // Instantiate Owner model
    }

    /**
     * Display a list of projects.
     */
    public function index(): void {
        $projects = $this->projectModel->findAll();
        $this->view->output('projects/list.html', [
            'pageTitle' => 'Projects',
            'activeNav' => 'projects',
            'projects' => $projects
        ]);
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(): void {
        $owners = $this->ownerModel->findAllSimple(); // Get owners for dropdown
        $this->view->output('projects/create.html', [
            'pageTitle' => 'Create New Project',
            'activeNav' => 'projects',
            'owners' => $owners,
            'project' => [], // Empty array for form partial compatibility
            'formAction' => '/projects/store' // Action for the form
        ]);
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(): void {
        // CSRF Check
        $submittedToken = $_POST[SecurityHelper::getFormInputName()] ?? null;
        if (!SecurityHelper::validateToken($submittedToken)) {
            $_SESSION['flash_error'] = 'Invalid request. Please try again.';
            $this->redirect('/projects/create');
            return;
        }

        // TODO: Add robust validation (required fields, types, uniqueness)
        $data = $_POST; // Get all POST data

        // Basic Validation Example
        if (empty($data['project_number']) || empty($data['project_name'])) {
            $_SESSION['flash_error'] = 'Project Number and Project Name are required.';
            $_SESSION['form_data'] = $data; // Store submitted data to repopulate form
            $this->redirect('/projects/create');
            return;
        }
        if ($this->projectModel->projectNumberExists($data['project_number'])) {
             $_SESSION['flash_error'] = 'Project Number already exists.';
             $_SESSION['form_data'] = $data;
             $this->redirect('/projects/create');
             return;
        }

        if ($this->projectModel->create($data)) {
            $_SESSION['flash_success'] = 'Project created successfully.';
            unset($_SESSION['form_data']); // Clear form data on success
            $this->redirect('/projects');
        } else {
            $_SESSION['flash_error'] = 'Failed to create project.';
            $_SESSION['form_data'] = $data;
            $this->redirect('/projects/create');
        }
    }

    /**
     * Display the specified project (optional view).
     */
    public function view(int $id): void {
        $project = $this->projectModel->findById($id);
        if (!$project) {
            $_SESSION['flash_error'] = 'Project not found.';
            $this->redirect('/projects');
            return;
        }

        // Potentially load related data (SOV, Billings, etc.) here

        $this->view->output('projects/view.html', [
            'pageTitle' => 'View Project: ' . htmlspecialchars($project['project_name']),
            'activeNav' => 'projects',
            'project' => $project
        ]);
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(int $id): void {
        $project = $this->projectModel->findById($id);
        if (!$project) {
            $_SESSION['flash_error'] = 'Project not found.';
            $this->redirect('/projects');
            return;
        }

        $owners = $this->ownerModel->findAllSimple(); // Get owners for dropdown

        // Use session data if validation failed on update attempt
        $formData = $_SESSION['form_data'] ?? $project;
        unset($_SESSION['form_data']);

        $this->view->output('projects/edit.html', [
            'pageTitle' => 'Edit Project: ' . htmlspecialchars($project['project_name']),
            'activeNav' => 'projects',
            'project' => $formData, // Use potentially repopulated data
            'owners' => $owners,
            'formAction' => '/projects/update/' . $id // Action for the form
        ]);
    }

    /**
     * Update the specified project in storage.
     */
    public function update(int $id): void {
        // CSRF Check
        $submittedToken = $_POST[SecurityHelper::getFormInputName()] ?? null;
        if (!SecurityHelper::validateToken($submittedToken)) {
            $_SESSION['flash_error'] = 'Invalid request. Please try again.';
            $this->redirect('/projects/edit/' . $id);
            return;
        }

        $project = $this->projectModel->findById($id);
        if (!$project) {
            $_SESSION['flash_error'] = 'Project not found.';
            $this->redirect('/projects');
            return;
        }

        // TODO: Add robust validation
        $data = $_POST;

         // Basic Validation Example
        if (empty($data['project_number']) || empty($data['project_name'])) {
            $_SESSION['flash_error'] = 'Project Number and Project Name are required.';
            $_SESSION['form_data'] = $data; // Store submitted data
            $this->redirect('/projects/edit/' . $id);
            return;
        }
         if ($this->projectModel->projectNumberExists($data['project_number'], $id)) {
             $_SESSION['flash_error'] = 'Project Number already exists.';
             $_SESSION['form_data'] = $data;
             $this->redirect('/projects/edit/' . $id);
             return;
        }

        if ($this->projectModel->update($id, $data) >= 0) { // Check >= 0 because 0 rows affected is not an error
            $_SESSION['flash_success'] = 'Project updated successfully.';
             unset($_SESSION['form_data']);
            $this->redirect('/projects/view/' . $id); // Redirect to view or list
        } else {
            $_SESSION['flash_error'] = 'Failed to update project.';
            $_SESSION['form_data'] = $data;
            $this->redirect('/projects/edit/' . $id);
        }
    }

    /**
     * Remove the specified project from storage.
     * Assumes POST request for deletion for CSRF protection.
     */
    public function delete(int $id): void {
         // CSRF Check
        $submittedToken = $_POST[SecurityHelper::getFormInputName()] ?? null;
        if (!SecurityHelper::validateToken($submittedToken)) {
            $_SESSION['flash_error'] = 'Invalid request. Please try again.';
            $this->redirect('/projects');
            return;
        }

        $project = $this->projectModel->findById($id);
        if (!$project) {
            $_SESSION['flash_error'] = 'Project not found.';
            $this->redirect('/projects');
            return;
        }

        if ($this->projectModel->delete($id) > 0) {
            $_SESSION['flash_success'] = 'Project deleted successfully.';
        } else {
            // Check foreign key constraints or other reasons for failure
            $_SESSION['flash_error'] = 'Failed to delete project. It might have related records (billings, SOV, etc.).';
        }
        $this->redirect('/projects');
    }
}