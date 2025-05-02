<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Helpers\SecurityHelper;
use App\Models\User;
use App\Database;

class UserController extends BaseController
{
    private User $userModel;
    protected string $controllerName = 'User';

    public function __construct(Database $db, array $config = [])
    {
        parent::__construct($db, $config);
        $this->userModel = new User($this->db);
    }

    /**
     * Show the login form.
     */
    public function showLogin(): void
    {
        if ($this->auth->isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        $this->render('auth/login', [
            'pageTitle' => 'Login'
        ]);
    }

    /**
     * Process the login form submission.
     */
    public function processLogin(): void
    {
        if ($this->auth->isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        if (!$this->checkCsrf('/login')) return;

        $identifier = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;

        if (empty($identifier) || empty($password)) {
            $this->setFlashMessage('error', 'Username/Email and Password are required.');
            $this->redirect('/login');
            return;
        }

        if ($this->auth->login($identifier, $password)) {
            unset($_SESSION['flash_error']);
            $this->redirect('/dashboard');
        } else {
            $this->setFlashMessage('error', 'Invalid credentials or inactive account.');
            $this->redirect('/login');
        }
    }

    /**
     * Log the user out.
     */
    public function logout(): void
    {
        $this->auth->logout();
        $this->setFlashMessage('success', 'You have been logged out.');
        $this->redirect('/login');
    }

    /**
     * List users (Admin only).
     */
    public function index(): void
    {
        $this->actionName = 'index';
        $this->requirePermission();

        $users = $this->userModel->findAll();

        $this->render('settings/users', [
            'pageTitle' => 'Manage Users',
            'activeNav' => 'settings-users',
            'users' => $users
        ]);
    }

    /**
     * Show form to create a new user (Admin only).
     */
    public function create(): void
    {
        $this->actionName = 'create';
        $this->requirePermission();

        $this->render('settings/users/create', [
            'pageTitle' => 'Create New User',
            'activeNav' => 'settings-users',
            'roles' => $this->userModel->getDefinedRoles(),
            'formData' => $_SESSION['form_data'] ?? [],
            'errors' => $_SESSION['errors'] ?? []
        ]);
        unset($_SESSION['form_data'], $_SESSION['errors']);
    }

    /**
     * Store a new user (Admin only).
     */
    public function store(): void
    {
        $this->actionName = 'store';
        $this->requirePermission();

        if (!$this->checkCsrf('/settings/users/create')) return;

        $data = $_POST['user'] ?? [];
        $password = $data['password'] ?? '';
        $passwordConfirmation = $data['password_confirmation'] ?? '';

        $errors = [];
        if (empty($data['username'])) $errors['username'] = 'Username is required.';
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid Email is required.';
        if (empty($data['first_name'])) $errors['first_name'] = 'First Name is required.';
        if (empty($data['last_name'])) $errors['last_name'] = 'Last Name is required.';
        if (empty($password)) $errors['password'] = 'Password is required.';
        if ($password !== $passwordConfirmation) $errors['password_confirmation'] = 'Passwords do not match.';
        if (empty($data['role']) || !in_array($data['role'], $this->userModel->getDefinedRoles())) $errors['role'] = 'Invalid Role selected.';
        if (!empty($data['username']) && $this->userModel->findByUsername($data['username'])) $errors['username'] = 'Username already exists.';
        if (!empty($data['email']) && $this->userModel->findByEmail($data['email'])) $errors['email'] = 'Email already exists.';

        if (!empty($errors)) {
            $_SESSION['form_data'] = $data;
            $_SESSION['errors'] = $errors;
            $this->setFlashMessage('error', 'Please correct the errors below.');
            $this->redirect('/settings/users/create');
            return;
        }

        $userData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'role' => $data['role'],
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 0,
            'password_hash' => $this->auth->hashPassword($password)
        ];

        if ($this->userModel->create($userData)) {
            $this->setFlashMessage('success', 'User created successfully.');
            $this->redirect('/settings/users');
        } else {
            $_SESSION['form_data'] = $data;
            $this->setFlashMessage('error', 'Failed to create user. Please try again.');
            $this->redirect('/settings/users/create');
        }
    }

    /**
     * View a specific user (Admin only).
     * @param int $id The ID of the user to view.
     */
    public function view(int $id): void
    {
        $this->actionName = 'view';
        $this->requirePermission();

        $user = $this->userModel->findById($id);

        if ($user) {
            $this->render('settings/users/view', [
                'pageTitle' => 'View User: ' . htmlspecialchars($user['username']),
                'activeNav' => 'settings-users',
                'user' => $user
            ]);
        } else {
            $this->setFlashMessage('error', 'User not found.');
            $this->redirect('/settings/users');
        }
    }

    /**
     * Show form to edit a specific user (Admin only).
     * @param int $id The ID of the user to edit.
     */
    public function edit(int $id): void
    {
        $this->actionName = 'edit';
        $this->requirePermission();

        $user = $this->userModel->findById($id);

        if ($user) {
            $formData = $_SESSION['form_data'] ?? $user;
            unset($_SESSION['form_data']);
            $errors = $_SESSION['errors'] ?? [];
            unset($_SESSION['errors']);

            $this->render('settings/users/edit', [
                'pageTitle' => 'Edit User: ' . htmlspecialchars($user['username']),
                'activeNav' => 'settings-users',
                'user' => $formData,
                'roles' => $this->userModel->getDefinedRoles(),
                'errors' => $errors
            ]);
        } else {
            $this->setFlashMessage('error', 'User not found.');
            $this->redirect('/settings/users');
        }
    }

    /**
     * Update a user (Admin only).
     * @param int $id The ID of the user to update.
     */
    public function update(int $id): void
    {
        $this->actionName = 'update';
        $this->requirePermission();

        if (!$this->checkCsrf("/settings/users/edit/$id")) return;

        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->setFlashMessage('error', 'User not found');
            $this->redirect('/settings/users');
            return;
        }

        $data = $_POST['user'] ?? [];
        $password = $data['password'] ?? '';
        $passwordConfirmation = $data['password_confirmation'] ?? '';

        $errors = [];
        if (empty($data['username'])) $errors['username'] = 'Username is required.';
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid Email is required.';
        if (empty($data['first_name'])) $errors['first_name'] = 'First Name is required.';
        if (empty($data['last_name'])) $errors['last_name'] = 'Last Name is required.';
        if (!empty($password) && $password !== $passwordConfirmation) $errors['password_confirmation'] = 'Passwords do not match.';
        if (empty($data['role']) || !in_array($data['role'], $this->userModel->getDefinedRoles())) $errors['role'] = 'Invalid Role selected.';
        if (!empty($data['username']) && $this->userModel->usernameExists($data['username'], $id)) $errors['username'] = 'Username already exists.';
        if (!empty($data['email']) && $this->userModel->emailExists($data['email'], $id)) $errors['email'] = 'Email already exists.';

        if (!empty($errors)) {
            $_SESSION['form_data'] = $data;
            $_SESSION['errors'] = $errors;
            $this->setFlashMessage('error', 'Please correct the errors below.');
            $this->redirect("/settings/users/edit/$id");
            return;
        }

        $updateData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'role' => $data['role'],
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 0,
        ];

        if (!empty($password)) {
            $updateData['password_hash'] = $this->auth->hashPassword($password);
        }

        if ($this->userModel->update($id, $updateData) >= 0) {
            $this->setFlashMessage('success', 'User updated successfully.');
            $this->redirect('/settings/users');
        } else {
            $_SESSION['form_data'] = $data;
            $this->setFlashMessage('error', 'Failed to update user. Please try again.');
            $this->redirect("/settings/users/edit/$id");
        }
    }

    /**
     * Delete a user (Admin only).
     * @param int $id The ID of the user to delete.
     */
    public function delete(int $id): void
    {
        $this->actionName = 'delete';
        $this->requirePermission();

        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->setFlashMessage('error', 'User not found.');
            $this->redirect('/settings/users');
            return;
        }

        if ($this->currentUser && $this->currentUser['id'] === $id) {
            $this->setFlashMessage('error', 'You cannot delete your own account.');
            $this->redirect('/settings/users');
            return;
        }

        if ($this->userModel->delete($id)) {
            $this->setFlashMessage('success', 'User deleted successfully.');
        } else {
            $this->setFlashMessage('error', 'Failed to delete user.');
        }
        $this->redirect('/settings/users');
    }

    // --- Password Reset Methods ---
    public function forgotPassword(): void
    {
        $this->render('auth/forgot_password', ['pageTitle' => 'Forgot Password']);
    }

    public function processForgotPassword(): void
    {
        // Add CSRF check
        // Validate email exists
        // Generate reset token, store it with expiry
        // Send email with reset link
        // Redirect with success/error message
    }

    public function resetPassword(string $token): void
    {
        // Validate token exists and hasn't expired
        // Render reset form, passing the token
        $this->render('auth/reset_password', ['pageTitle' => 'Reset Password', 'token' => $token]);
    }

    public function processResetPassword(string $token): void
    {
        // Add CSRF check
        // Validate token exists and hasn't expired
        // Validate new password and confirmation
        // Update user's password hash
        // Invalidate the reset token
        // Redirect to login with success message
    }
}