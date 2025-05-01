<?php

namespace App\Controllers;

use App\Controller; // Extends base Controller
use App\Models\User;
use App\Helpers\SecurityHelper; // Import SecurityHelper

class UserController extends Controller {

    private User $userModel;

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
        if (!$this->auth->checkRole('admin')) {
             $_SESSION['flash_error'] = 'Access Denied.';
             $this->redirect('/dashboard'); // Or show a 403 page
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
         if (!$this->auth->checkRole('admin')) {
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
         if (!$this->auth->checkRole('admin')) {
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

    // Add edit, update, delete methods similarly with permission checks

    // --- Password Reset Methods ---
    public function forgotPassword(): void { /* Show form */ }
    public function processForgotPassword(): void { /* Handle submission, generate token, send email */ }
    public function resetPassword(string $token): void { /* Show reset form if token is valid */ }
    public function processResetPassword(string $token): void { /* Handle reset submission */ }

     // TODO: Add CSRF checks to all other POST/UPDATE/DELETE methods (update, delete, processForgotPassword, processResetPassword etc.)
}