<?php

namespace AppControllers;

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
            $this->view->render('project/list.html', ['projects' => $projects]);
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
            $project = new Project($this->db);
            $project = $project->find($id);
            if(!$project){
                include(__DIR__ . '/../../templates/404.html');
                return;
            }
            $owner = new Owner($this->db);
            $this->view->render('project/view.html', ['project' => $project, 'owner' => $owner]);
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
        $project = new Project($this->db);
        $this->before('create');
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
     * @return string The project ID.
     */
    public function delete(int $id): string
    {

        $this->before('delete');
        try{
            $project = new Project($this->db);
            $project = $project->find($id);
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
}