<?php

namespace App\Controllers;

use App\Database;
use App\Libraries\Auth;
use App\Models\SOV;
use App\Models\Project;
use App\Controllers\BaseController;

class ScheduleOfValuesController extends BaseController
{
    // Assuming $this->controller is set correctly, perhaps in BaseController
    // private $controller = 'ScheduleOfValues'; // Example if not set elsewhere

    public function __construct(Database $db)
    {
        parent::__construct($db);
        // It might be better to set $this->controller here if not done in BaseController
        // $this->controller = 'ScheduleOfValues'; 
    }

    /**
     * Executes before controller actions.
     * Checks authentication and authorization.
     *
     * @param string $action The name of the action being called.
     * @return void
     */
    protected function before(string $action)
    {
        // Check if the user is logged in
        if (!Auth::isLoggedIn()) {    
            // Redirect to the login page if not logged in
            $this->redirect('/login');
            exit;
        }

        // Get the current user from the session
        // Ensure session is started before accessing $_SESSION
        if (session_status() === PHP_SESSION_NONE) {
            session_start(); 
        }
        // Check if user is actually set in session
        if (!isset($_SESSION['user'])) {
             $this->redirect('/login');
             exit;
        }
        $user = $_SESSION['user'];

        // Check if the user has permission to access the current controller and action
        // Use the passed $action parameter for reliability
        if (!Auth::checkPermission($user, $this->controller, $action)) {
            // Redirect to a 403 error page if no permission
            $this->redirect('/403');
            exit;
        }
    }

    public function index($projectId)
    {
        $this->before('index'); // Pass action name
        $project = Project::find($projectId, $this->db);
        if (!$project) {
            $this->redirect('/404');
            exit;
        }
        $scheduleOfValues = SOV::where('project_id', $projectId, $this->db)->get();
        return $this->render('schedule-of-values/index.html', ['schedule_of_values' => $scheduleOfValues, 'project' => $project]);
    }

    public function view($id)
    {
        $this->before('view'); // Pass action name
        $scheduleOfValue = SOV::find($id, $this->db);
        if (!$scheduleOfValue) {
            $this->redirect('/404');
            exit;
        }
        return $this->render('schedule-of-values/view.html', ['schedule_of_value' => $scheduleOfValue]);
    }

    public function create($data)
    {
        $this->before('create'); // Pass action name
        $scheduleOfValue = new SOV($this->db, $data);
        // It's good practice to check if save was successful
        if ($scheduleOfValue->save()) {
            return $scheduleOfValue->id;
        }
        // Handle error case, e.g., return false or throw exception
        return false; 
    }

    public function edit($id, $data)
    {
        $this->before('edit'); // Pass action name
        $scheduleOfValue = SOV::find($id, $this->db);
        if (!$scheduleOfValue) {
            $this->redirect('/404');
            exit;
        }
        $scheduleOfValue->fill($data);
        // It's good practice to check if save was successful
        if ($scheduleOfValue->save()) {
            return $scheduleOfValue->id;
        }
         // Handle error case, e.g., return false or throw exception
        return false;
    }

    public function delete($id)
    {
        $this->before('delete'); // Pass action name
        $scheduleOfValue = SOV::find($id, $this->db);
        if (!$scheduleOfValue) {
            $this->redirect('/404');
            exit;
        }
        // It's good practice to check if delete was successful
        if ($scheduleOfValue->delete()) {
             return $scheduleOfValue->id; // Or return true
        }
        // Handle error case, e.g., return false or throw exception
        return false;
    }
}