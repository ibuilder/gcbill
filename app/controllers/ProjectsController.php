<?php

namespace App\Controllers;

use App\Libraries\Auth;
use Exception;
use App\Database;
use App\Models\Project;
use App\Models\Owner;

class ProjectsController
{
    protected $controller = 'Projects';
    protected $action;
    protected $db;

    public function __construct(Database $db)
    {
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
            $projects = Project::all();
            include(__DIR__ . '/../../templates/projects/index.html');
        }catch(Exception $e){
            error_log('Error in ProjectsController::index: ' . $e->getMessage());
            include(__DIR__ . '/../../templates/error.html');
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
            $project = Project::find($id);
            if(!$project){
                include(__DIR__ . '/../../templates/404.html');
                return;
            }
            $owners = Owner::all();
            include(__DIR__ . '/../../templates/projects/view.html');
        }catch(Exception $e){
            error_log('Error in ProjectsController::view: ' . $e->getMessage());
            include(__DIR__ . '/../../templates/error.html');
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
        $this->before('create');        
        try{
            $project = new Project();
            foreach ($data as $key => $value) {
                $project->$key = $value;
            }
            $result = $project->create($data);
            if(!$result){
                include(__DIR__ . '/../../templates/error.html');
                return "";
            }
            return "{$result}";
        }catch(Exception $e){
            error_log('Error in ProjectsController::create: ' . $e->getMessage());
            include(__DIR__ . '/../../templates/error.html');
            return "";
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
            $project = Project::find($id);
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
     * @return string The project ID.
     */
    public function delete(int $id): string
    {
        $this->before('delete');
        try{
            $project = Project::find($id);
            if(!$project){
                include(__DIR__ . '/../../templates/404.html');
                return "";
            }
            $result = $project->delete($id);
            if(!$result){
                include(__DIR__ . '/../../templates/error.html');
                return "";
            }
            return "{$id}";
        }catch(Exception $e){
            error_log('Error in ProjectsController::delete: ' . $e->getMessage());
            include(__DIR__ . '/../../templates/error.html');
            return "";
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
        if (method_exists($this, $method)) {
            call_user_func_array([$this, $method], $args);
        } else {
            header('HTTP/1.1 404 Not Found');
        }
    }