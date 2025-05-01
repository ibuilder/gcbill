php
<?php

namespace App\Controllers;

use App\Libraries\Auth;

class StaffTimeEntriesController extends Controller
{
    public function before()
    {
        $auth = new Auth();
        $user = $auth->getCurrentUser();
        if (!$user) {
            header('Location: /login');
            exit;
        }
        $controller = str_replace('Controller', '', get_class($this));
        $action = $this->route_params['action'];

        if (!$auth->checkPermission($user, $controller, $action)) {
            http_response_code(403);
            echo "<h1>403 Forbidden</h1>";
            exit;
        }
    }

    public function index($projectId)
    {
        $this->before();
        
        echo "Staff Time Entries Index for project {$projectId}";
    }

    public function view($projectId, $id)
    {
        $this->before();

        echo "View Staff Time Entry {$id} for project {$projectId}";
    }

    public function create($data)
    {
        $this->before();

        echo "Create Staff Time Entry: " . json_encode($data);
    }

    public function edit($id, $data)
    {
        $this->before();

        echo "Edit Staff Time Entry {$id}: " . json_encode($data);
    }

    public function delete($id)
    {
        $this->before();

        echo "Delete Staff Time Entry {$id}";
    }
}