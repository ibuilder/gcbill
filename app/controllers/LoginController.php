<?php

namespace AppControllers;

use AppLibrariesAuth;
use AppModelsUser;
use AppDatabase;

class LoginController extends BaseController
{
    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    public function index()
    {
        if (Auth::isLoggedIn()) {
            header('Location: /');
            exit;
        }

        return $this->view('auth/login');
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
        
        $userModel = new User($this->db);
        $user = $userModel->getByUsername($username);

        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: /login');
            exit;
        }
        
        if (password_verify($password, $user->password)) {
            Auth::login($user);
            header('Location: /');
            exit;
        } else {
            $_SESSION['error'] = 'Invalid username or password.';
            header('Location: /login');
            exit;
        }
    }
}