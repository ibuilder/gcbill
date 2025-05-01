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

    public function before($action)
    {
        $user = $this->auth->getCurrentUser();
        if (!$user) {
            header('Location: /login');
            exit;
        }
        if (!$this->auth->checkPermission($user, 'ProjectsController', $action)) {
            header('Location: /403');
            exit;
        }
    }

    public function index()
    {
        $this->before('index');
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
        $this->before('create');
        $project = new Project();
        foreach ($data as $key => $value) {
            $project->$key = $value;
        }
        $project->save();
        return "{$project->id}";
    }

    public function edit($id, $data = [])
    {
        $this->before('edit');
        $project = Project::find($id);
        foreach ($data as $key => $value) {
            $project->$key = $value;
        }
        $project->save();
        return "{$project->id}";
    }

    public function delete($id)
    {
        $this->before('delete');
        $project = Project::find($id);
        $project->delete();
        return "{$project->id}";
    }
}