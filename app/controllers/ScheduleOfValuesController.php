<?php

namespace AppControllers;

use App\Database;
use App\Libraries\Auth;
use App\Models\SOV;
use App\Models\Project;use App\Controllers\BaseController;

class ScheduleOfValuesController extends BaseController
{
    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    protected function before($action)
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

    public function index($projectId)
    {
        $this->before('index');
        $project = Project::find($projectId, $this->db);
        if (!$project) {
            header('Location: /404');
            exit;
        }
        $scheduleOfValues = SOV::where('project_id', $projectId, $this->db)->get();
        return $this->view('schedule-of-values/index.html', ['schedule_of_values' => $scheduleOfValues, 'project' => $project]);
    }

    public function view($id)
    {
        $this->before();
        $scheduleOfValue = SOV::find($id, $this->db);
        if (!$scheduleOfValue, $this->db) {
            header('Location: /404');
            exit;
        }
        return $this->view('schedule-of-values/view.html', ['schedule_of_value' => $scheduleOfValue]);
    }

    public function create($data)
    {
        $this->before();
        $scheduleOfValue = new SOV($this->db,$data);
        $scheduleOfValue->save();
        return $scheduleOfValue->id;
    }

    public function edit($id, $data)
    {
        $this->before();
        $scheduleOfValue = SOV::find($id, $this->db);
        if (!$scheduleOfValue) {
            header('Location: /404');
            exit;
        }
        $scheduleOfValue->fill($data);
        $scheduleOfValue->save();
        return $scheduleOfValue->id;
    }

    public function delete($id)
    {
        $this->before();
        $scheduleOfValue = SOV::find($id, $this->db);
        if (!$scheduleOfValue) {
            header('Location: /404');
            exit;
        }
        $scheduleOfValue->delete();
        return $scheduleOfValue->id;
    }
}