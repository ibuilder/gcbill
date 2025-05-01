<?php

namespace App\Controllers;

use App\Controller; // Extends base Controller
use App\Models\User;
use App\Helpers\SecurityHelper; // Import SecurityHelper
use App\Libraries\Auth;

/** */
class UserController extends Controller {

    private User $userModel;
    private const USERS_CONTROLLER = 'UserController';

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

    public function __construct() {
        parent::__construct(); // Calls parent constructor (DB, View, Auth)
        $this->userModel = new User($this->db); // Instantiate User model
    }

    /**
     * Show the login form.
     */
    public function login(): void {
        // If already logged in, redirect to dashboard
        if ($this->auth->isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        $this->view->output('auth/login.html');
    }

    /**
     * Process the login form submission.
     */
    public function processLogin(): void {
         // If already logged in, redirect
        if ($this->auth->isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        // --- CSRF Check ---
        $submittedToken = $_POST[SecurityHelper::getFormInputName()] ?? null;
        if (!SecurityHelper::validateToken($submittedToken)) {
            // CSRF token is invalid or missing
            $_SESSION['flash_error'] = 'Invalid request. Please try again.';
            $this->redirect('/login');
            return; // Stop execution
        }
        // --- End CSRF Check ---

        // Basic validation (implement more robust validation later)
        $identifier = $_POST['identifier'] ?? null; // Can be username or email
        $password = $_POST['password'] ?? null;

        if (empty($identifier) || empty($password)) {
            // Set flash message for error (implement flash message system)
            $_SESSION['flash_error'] = 'Username/Email and Password are required.';
            $this->redirect('/login');
            return;
        }

        if ($this->auth->login($identifier, $password)) {
            // Login successful
             unset($_SESSION['flash_error']); // Clear any previous error
            $this->redirect('/dashboard'); // Redirect to dashboard or intended page
        } else {
            // Login failed
            $_SESSION['flash_error'] = 'Invalid credentials or inactive account.';
            $this->redirect('/login');
        }
    }

    /**
     * Log the user out.
     */
    public function logout(): void {
        $this->auth->logout();
        // Set flash message for success (optional)
        $_SESSION['flash_success'] = 'You have been logged out.';
        $this->redirect('/login');
    }

    // --- User Management Methods (Example - Add permission checks) ---

    /**
     * List users (Admin only).
     */
    public function index(): void {
        // Permission Check
        $this->before();
        if (!$this->auth->checkPermission($this->auth->getUser($_SESSION['user_id']),self::USERS_CONTROLLER, __FUNCTION__)) {
            $_SESSION['flash_error'] = 'Access Denied.';
            $this->redirect('/dashboard');
           return;
        }

        // Fetch users (implement pagination later)
        $users = $this->db->select("SELECT id, username, email, first_name, last_name, role, is_active FROM users ORDER BY last_name, first_name");

        $this->view->output('settings/users/list.html', ['users' => $users]); // Adjust template path
    }

    /**
     * Show form to create a new user (Admin only).
     */
    public function create(): void {
        // Permission Check
        $this->before();
         if (!$this->auth->checkPermission($this->auth->getUser($_SESSION['user_id']),self::USERS_CONTROLLER, __FUNCTION__)) {
            $_SESSION['flash_error'] = 'Access Denied.';
            $this->redirect('/dashboard');
            return;
        }
         $this->view->output('settings/users/create.html'); // Adjust template path
    }

    /**
     * Store a new user (Admin only).
     */
    public function store(): void {
        $this->before();
        // Permission Check
        if (!$this->auth->checkPermission($this->auth->getUser($_SESSION['user_id']),self::USERS_CONTROLLER, __FUNCTION__)) {
            $_SESSION['flash_error'] = 'Access Denied.';
             $this->redirect('/dashboard');
             return;
         }

         // --- CSRF Check ---
         $submittedToken = $_POST[SecurityHelper::getFormInputName()] ?? null;
         if (!SecurityHelper::validateToken($submittedToken)) {
             $_SESSION['flash_error'] = 'Invalid request. Please try again.';
             $this->redirect('/settings/users/create'); // Redirect back to form
             return;
         }
         // --- End CSRF Check ---

         // TODO: Add Input Validation & CSRF Check
         $data = [
             'username' => $_POST['username'] ?? null,
             'email' => $_POST['email'] ?? null,
             'first_name' => $_POST['first_name'] ?? null,
             'last_name' => $_POST['last_name'] ?? null,
             'role' => $_POST['role'] ?? 'staff',
             'is_active' => isset($_POST['is_active']) ? 1 : 0,
             'password_hash' => $this->auth->hashPassword($_POST['password'] ?? '') // Hash the password
         ];

         // Basic check for required fields
         if (empty($data['username']) || empty($data['email']) || empty($_POST['password'])) {
              $_SESSION['flash_error'] = 'Username, Email, and Password are required.';
              // Pass back input data to repopulate form (implement this)
              $this->redirect('/settings/users/create');
              return;
         }

         if ($this->userModel->create($data)) {
             $_SESSION['flash_success'] = 'User created successfully.';
             $this->redirect('/settings/users');
         } else {
             $_SESSION['flash_error'] = 'Failed to create user (e.g., duplicate username/email).';
             $this->redirect('/settings/users/create');
         }
    }

        /**
     * View a specific user (Admin only).
     * @param int $id The ID of the user to view.
     */
    public function view(int $id): void {
         $this->before();
         if (!$this->auth->checkPermission($this->auth->getUser($_SESSION['user_id']),self::USERS_CONTROLLER, __FUNCTION__)) {
             $_SESSION['flash_error'] = 'Access Denied.';
             $this->redirect('/dashboard');
             return;
         }

        $user = $this->userModel->find($id);

        if ($user) {
            $this->view->output('settings/users/view.html', ['user' => $user]);
        } else {
            $_SESSION['flash_error'] = 'User not found.';
            $this->redirect('/settings/users');
        }
    }

    /**
     * Edit a specific user (Admin only).
     * @param int $id The ID of the user to edit.
     */
    public function edit(int $id): void {
         $this->before();
         if (!$this->auth->checkPermission($this->auth->getUser($_SESSION['user_id']),self::USERS_CONTROLLER, __FUNCTION__)) {
             $_SESSION['flash_error'] = 'Access Denied.';
             $this->redirect('/dashboard');
             return;
         }
         $user = $this->userModel->find($id);
         
        if ($user) {
            $this->view->output('settings/users/edit.html', ['user' => $user]);
        } else {
            $_SESSION['flash_error'] = 'User not found.';
            $this->redirect('/settings/users');
        }
    }

    /**
     * Update a user (Admin only).
     * @param int $id The ID of the user to update.
     * @param array $data The updated user data.
     */
    public function update(int $id, array $data): void {
         $this->before();
         if (!$this->auth->checkPermission($this->auth->getUser($_SESSION['user_id']),self::USERS_CONTROLLER, __FUNCTION__)) {
             $_SESSION['flash_error'] = 'Access Denied.';
             $this->redirect('/dashboard');
             return;
         }

         // --- CSRF Check ---
         $submittedToken = $_POST[SecurityHelper::getFormInputName()] ?? null;
         if (!SecurityHelper::validateToken($submittedToken)) {
             $_SESSION['flash_error'] = 'Invalid request. Please try again.';
             $this->redirect("/settings/users/$id/edit"); // Redirect back to form
             return;
         }
         // --- End CSRF Check ---

        // Implement input validation and CSRF checks here
        $user = $this->userModel->find($id);
        if (empty($user)) {
            $_SESSION['flash_error'] = 'User not found';
            $this->redirect('/settings/users');

            return;
        }
        // Implement input validation and CSRF checks here
        if ($this->userModel->update($id, $data)) {
            $_SESSION['flash_success'] = 'User updated successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to update user.';
        }
        $this->redirect('/settings/users'); // Redirect to user list
    }

    /**
     * Delete a user (Admin only).
     * @param int $id The ID of the user to delete.
     */
    public function delete(int $id): void {
        $this->before();
         if (!$this->auth->checkPermission($this->auth->getUser($_SESSION['user_id']),self::USERS_CONTROLLER, __FUNCTION__)) {
             $_SESSION['flash_error'] = 'Access Denied.';
             $this->redirect('/dashboard');
             return;
         }
         $this->userModel->delete($id);
        $_SESSION['flash_success'] = 'User deleted successfully';
         $this->redirect('/settings/users'); // Redirect to user list
        
    }

    // Add edit, update, delete methods similarly with permission checks

    // --- Password Reset Methods ---
    public function forgotPassword(): void { /* Show form */ }
    public function processForgotPassword(): void { /* Handle submission, generate token, send email */ }
    public function resetPassword(string $token): void { /* Show reset form if token is valid */ }
    public function processResetPassword(string $token): void { /* Handle reset submission */ }

     // TODO: Add CSRF checks to all other POST/UPDATE/DELETE methods (update, delete, processForgotPassword, processResetPassword etc.)
}