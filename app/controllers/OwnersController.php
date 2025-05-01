php
<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Models\Owner;

class OwnersController extends Controller
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

    public function index()
    {
        $this->before();
        $owners = Owner::all();
        echo json_encode($owners);
    }

    public function view()
    {
        $this->before();
        $id = $this->route_params['id'];
        $owner = Owner::find($id);
        echo json_encode($owner);
    }

    public function create($data)
    {
        $this->before();
        $owner = new Owner($data);
        $owner->save();
        echo json_encode(['id' => $owner->id]);
    }

    public function edit($id, $data)
    {
        $this->before();
        $owner = Owner::find($id);
        $owner->fill($data);
        $owner->save();
        echo json_encode(['id' => $owner->id]);
    }

    public function delete($id)
    {
        $this->before();
        $owner = Owner::find($id);
        $owner->delete();
        echo json_encode(['id' => $id]);
    }
}