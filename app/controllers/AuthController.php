<?php
namespace App\Controllers;

use App\BaseController; // Ensure BaseController exists and is in the correct namespace
use App\Helpers\AuthHelper;

class AuthController extends BaseController {
    protected AuthHelper $auth;

    public function __construct() {
        parent::__construct();
        $this->auth = new AuthHelper($this->db);
    }

    public function login() {
        $this->view->output('login', ['flashMessages' => $this->getFlashMessages() ?? []]);
    }
}