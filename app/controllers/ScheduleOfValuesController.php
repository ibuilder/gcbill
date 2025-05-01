php
<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Models\ScheduleOfValues;
use App\Models\Project;

class ScheduleOfValuesController extends BaseController
{
    public function before()
    {
        $auth = new Auth();
        $user = $auth->getLoggedUser();
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $controller = get_class($this);
        $action = $this->current_action;

        if (!$auth->checkPermission($user, $controller, $action)) {
            header('Location: /403');
            exit;
        }
    }

    public function index($projectId)
    {
        $this->before();
        $project = Project::find($projectId);
        if (!$project) {
            header('Location: /404');
            exit;
        }
        $scheduleOfValues = ScheduleOfValues::where('project_id', $projectId)->get();
        return $this->view('schedule-of-values/index.html', ['schedule_of_values' => $scheduleOfValues, 'project' => $project]);
    }

    public function view($id)
    {
        $this->before();
        $scheduleOfValue = ScheduleOfValues::find($id);
        if (!$scheduleOfValue) {
            header('Location: /404');
            exit;
        }
        return $this->view('schedule-of-values/view.html', ['schedule_of_value' => $scheduleOfValue]);
    }

    public function create($data)
    {
        $this->before();
        $scheduleOfValue = new ScheduleOfValues($data);
        $scheduleOfValue->save();
        return $scheduleOfValue->id;
    }

    public function edit($id, $data)
    {
        $this->before();
        $scheduleOfValue = ScheduleOfValues::find($id);
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
        $scheduleOfValue = ScheduleOfValues::find($id);
        if (!$scheduleOfValue) {
            header('Location: /404');
            exit;
        }
        $scheduleOfValue->delete();
        return $scheduleOfValue->id;
    }
}