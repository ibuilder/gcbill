<?php

namespace App\Controllers;

use App\Libraries\Auth;

class StaffTimeEntriesController extends Controller
{
    public function before()
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