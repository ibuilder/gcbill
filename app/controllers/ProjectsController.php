<?php

namespace App\Controllers;

use App\Libraries\Auth;
use Exception;
use App\Database;
use App\Models\Project;
use App\Models\Owner;

class ProjectsController extends \App\Controllers\BaseController
{
    protected $controller = 'Projects';
    protected $action;

    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    public function before($action)
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
            header('Location: /403');
            exit;
        }
    }

    /**
     * Index action: Show all projects.
     * @return void
     */
    public function index(): void
    {
        $this->before('index');
        try{
            $project = new Project($this->db);
            $projects = $project->all();
            // Corrected: Use .php extension
            $this->render('project/list.php', ['projects' => $projects]);
        }catch(Exception $e){
            error_log('Error in ProjectsController::index: ' . $e->getMessage());
            // Corrected: Use render for error page
            $this->render('errors/500.php', ['message' => 'An unexpected error occurred.']);
        }
    }

    /**
     * View action: Show a specific project.
     * @param int $id The project ID.
     * @return void
     */
    public function view(int $id): void {
        $this->before('view');
        try{
            $project = new Project($this->db);
            $project = $project->find($id);
            if(!$project){
                // Corrected: Use render for 404 page
                $this->render('errors/404.php');
                return;
            }
            $owner = new Owner($this->db);
            // Corrected: Use .php extension
            $this->render('project/view.php', ['project' => $project, 'owner' => $owner]);
        }catch(Exception $e){
            error_log('Error in ProjectsController::view: ' . $e->getMessage());
            // Corrected: Use render for error page
            $this->render('errors/500.php', ['message' => 'An unexpected error occurred while loading the project.']);
        }
    }

    /**

    /**
     * Create action: Create a new project.
     * @param array $data The project data.
     * @return string The project ID.
     */
    public function create(array $data): string
    {
        // ... (validation and other logic) ...
        try { // Added try-catch block
            $project = new Project($this->db);
            $this->before('create');
            $result = $project->create($data);
            if(!$result){
                // Corrected: Use render for error page (or set flash message and redirect)
                $this->setFlashMessage('error', 'Failed to create project.');
                // Redirecting might be better here than rendering an error directly
                $this->redirect('/projects/create'); // Example redirect
                return ""; // Return empty or handle differently
            }
            return "{$result}";
        }catch(Exception $e){
            error_log('Error in ProjectsController::create: ' . $e->getMessage());
            // Corrected: Use render for error page (or set flash message and redirect)
            $this->setFlashMessage('error', 'An unexpected error occurred while creating the project.');
            $this->redirect('/projects/create'); // Example redirect
            return ""; // Return empty or handle differently
        }
    }

    /**
     * Edit action: Update an existing project.
     * @param int $id The project ID.
     * @param array $data The project data.
     * @return string The project ID.
     */
    public function edit(int $id, array $data): string
    {

        $this->before('edit');
        try{
            $project = new Project($this->db);
            $project = $project->find($id);
            if(!$project){
                include(__DIR__ . '/../../templates/404.html');
                return "";
            }
            $result = $project->update($id, $data);
            if(!$result){
                include(__DIR__ . '/../../templates/error.html');
                return "";
            }
            return "{$id}";
        }catch(Exception $e){
            error_log('Error in ProjectsController::edit: ' . $e->getMessage());
            include(__DIR__ . '/../../templates/error.html');
            return "";
        }
    }

    /**
     * Delete action: Delete a project.
     * @param int $id The project ID.
     * @return void
     */
    public function delete(int $id): void // Changed return type hint
    {
        // ... (permission checks, CSRF checks) ...
        try { // Added try-catch block
            $project = new Project($this->db); // Assuming Project model is instantiated
            $project = $project->find($id); // Assuming find method exists
            if(!$project){
                // Corrected: Use render for 404 page (or set flash message and redirect)
                $this->setFlashMessage('error', 'Project not found.');
                $this->redirect('/projects'); // Example redirect
                return; // Added return
            }
            $result = $project->delete($id); // Assuming delete method exists
            if(!$result){
                // Corrected: Use render for error page (or set flash message and redirect)
                $this->setFlashMessage('error', 'Failed to delete project.');
                $this->redirect('/projects/view/' . $id); // Example redirect
                return; // Added return
            }
            // Corrected: Redirect after successful deletion
            $this->setFlashMessage('success', 'Project deleted successfully.');
            $this->redirect('/projects');
            // Removed return "{$id}"; - redirect is usually preferred
        }catch(Exception $e){
            error_log('Error in ProjectsController::delete: ' . $e->getMessage());
            // Corrected: Use render for error page (or set flash message and redirect)
            $this->setFlashMessage('error', 'An unexpected error occurred while deleting the project.');
            $this->redirect('/projects'); // Example redirect
            // Removed return "";
        }
    }
}