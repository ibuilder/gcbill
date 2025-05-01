php
<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Models\Owner;

class OwnersController extends Controller
{
    public function before()
    {
        $auth = new Auth();
        $user = $auth->getCurrentUser();

        if (!$user) {
            header('Location: /login');
            exit();
        }

        $controller = 'OwnersController';
        $action = $this->route_params['action'];

        if (!$auth->checkPermission($user, $controller, $action)) {
            header('Location: /403');
            exit();
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