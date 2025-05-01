php
<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Models\Project;
use App\Models\Owner;

class ProjectsController
{
    private $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    protected function before()
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
            header('Location: /403');
            exit;
        }
    }

    public function index()
    {
        $this->before();
        $projects = Project::all();
        include(__DIR__ . '/../../templates/projects/index.html');
    }

    public function view($id)
    {
        $this->before('view');
        $project = Project::find($id);
        $owners = Owner::all();
        include(__DIR__ . '/../../templates/projects/view.html');
    }

    public function create($data = [])
    {
        $this->before();
        $project = new Project();
        foreach ($data as $key => $value) {
            $project->$key = $value;
        }
        $project->save();
        return "{$project->id}";
    }

    public function edit($id, $data = [])
    {
        $this->before();
        $project = Project::find($id);
        foreach ($data as $key => $value) {
            $project->$key = $value;
        }
        $project->save();
        return "{$project->id}";
    }

    public function delete($id)
    {
        $this->before();
        $project = Project::find($id);
        $project->delete();
        return "{$project->id}";
    }
}