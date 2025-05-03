<?php

namespace App\Controllers; // Assuming this is the correct namespace

use App\Helpers\AuthHelper; // Ensure correct path
use App\Helpers\SecurityHelper; // Ensure correct path

class AuthController extends BaseController {
    protected AuthHelper $auth; // Type declaration

    public function __construct() {
        parent::__construct(); // Ensure parent constructor is called
        $this->auth = new AuthHelper($this->db); // Instantiate AuthHelper
    }

    public function login() {
        // moved to base controller.

        $this->view->output('login', ['flashMessages' => $this->getFlashMessages() ?? [], ]);
    }

}
}
