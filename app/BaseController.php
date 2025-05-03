<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\SecurityHelper;

class AuthController extends BaseController
{
    protected AuthHelper $auth;

    public function __construct()
    {
        parent::__construct();
        $this->auth = new AuthHelper($this->db);
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!SecurityHelper::validateToken($_POST[SecurityHelper::getFormInputName()])) {
                $this->db->setFlashMessage('error', 'Invalid CSRF token.');
                $this->db->redirect('/login');
            }
            // ... rest of your login logic ...
        }

        $this->view->output('login', [
            'flashMessages' => $this->getFlashMessages() ?? [],
        ]);
    }

//... rest of your AuthController
}
