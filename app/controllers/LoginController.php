php
<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Models\User;

class LoginController extends BaseController
{
    public function index()
    {
        if (Auth::isLoggedIn()) {
            header('Location: /');
            exit;
        }

        return $this->view('login/index');
    }

    public function login($data)
    {
        if (Auth::isLoggedIn()) {
            header('Location: /');
            exit;
        }

        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        if (!$username || !$password) {
            $_SESSION['error'] = 'Please enter your username and password.';
            header('Location: /login');
            exit;
        }
        
        $userModel = new User();
        $user = $userModel->getByUsername($username);

        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: /login');
            exit;
        }
        

        if (Auth::login($username, $password)) {
            header('Location: /');
            exit;
        } else {
            $_SESSION['error'] = 'Invalid username or password.';
            header('Location: /login');
            exit;
        }
    }
}