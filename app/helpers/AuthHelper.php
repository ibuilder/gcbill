<?php

namespace App\Helpers;

use App\Database;
use App\Models\User; // Use the User model

class AuthHelper
{
    private Database $db;
    private User $userModel;
    private string $sessionKey = 'user'; // Key to store user info in session

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->userModel = new User($this->db); // Pass DB instance to model

        // Ensure session is started (might be redundant if started elsewhere, e.g., bootstrap)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Attempt to log in a user.
     * @param string $identifier Username or email
     * @param string $password Plain text password
     * @return bool True on success, false on failure
     */
    public function login(string $identifier, string $password): bool
    {
        $user = $this->userModel->findByIdentifier($identifier);

        // Ensure $user is an array and password_hash exists before verification
        if ($user && isset($user->password_hash) && $user->is_active && password_verify($password, $user->password_hash)) {
            // Password matches and user is active
            $this->setSession($user->getAttributes()); // Pass the user array
            // Update last login timestamp (optional)
            // Ensure update method exists and handles potential errors
            $this->userModel->update($user->id, ['last_login_at' => date('Y-m-d H:i:s')]);
            return true;
        }

        return false; // Login failed
    }

    /**
     * Log the current user out.
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start(); // Ensure session exists before unsetting/destroying
        }
        unset($_SESSION[$this->sessionKey]);
        // Optionally destroy the entire session if needed, but be careful if other session data exists
        // session_destroy();
        // Regenerate ID after logout for security is also a good practice if not destroying
        session_regenerate_id(true);
    }

    /**
     * Check if there is a user currently logged in.
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        // Ensure session is started before checking
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION[$this->sessionKey]) && !empty($_SESSION[$this->sessionKey]);
    }

    /**
     * Get the currently logged-in user's data.
     * @return array User data array or empty array if not logged in.
     */
    public function getCurrentUser(): array
    {
        if (!$this->isLoggedIn()) {
            return [];
        }
        return $_SESSION[$this->sessionKey];
    }

    /**
     * Get the ID of the currently logged-in user.
     * @return int|null User ID or null if not logged in or ID not set.
     */
    public function getUserId(): ?int
    {
        $user = $this->getCurrentUser();
        // Ensure 'id' key exists and is numeric
        return isset($user['id']) && is_numeric($user['id']) ? (int)$user['id'] : null;
    }

    /**
     * Set the user session data.
     * @param array $userData User data array from the database/model.
     */
    private function setSession(array $userData): void
    {
        // Regenerate session ID for security upon login
        session_regenerate_id(true);
        // Store relevant user data, avoid storing sensitive info like password hash
        // Example: Store ID, username, email, role, name
        $_SESSION[$this->sessionKey] = [
            'id' => $userData['id'],
            'username' => $userData['username'] ?? null,
            'email' => $userData['email'] ?? null,
            'role' => $userData['role'] ?? null,
            'name' => $userData['name'] ?? null,
            'permissions' => isset($userData['permissions']) ? json_decode($userData['permissions'], true) : [],
            // Add other necessary non-sensitive fields
        ];
    }

    /**
     * Hash a password using configured algorithm.
     * Consider moving config loading out of the method if used frequently.
     *
     * @param string $password
     * @return string Hashed password
     */
    public function hashPassword(string $password): string
    {
        // Using global $config is generally discouraged. Prefer dependency injection.
        // Assuming $config is loaded elsewhere (e.g., bootstrap.php)
        global $config;
        $algo = $config['security']['password_hash_algo'] ?? PASSWORD_DEFAULT;
        $options = $config['security']['password_hash_options'] ?? [];
        return password_hash($password, $algo, $options);
    }

    /**
     * Check if the current user has one of the specified roles.
     * @param string|array $roles A single role string or an array of allowed roles.
     * @return bool True if the user has one of the roles, false otherwise.
     */
    public function checkRole(string|array $roles): bool
    {
        $user = $this->getCurrentUser();
        if (!$user || !isset($user['role'])) {
            return false; // Not logged in or role not set
        }

        $userRole = $user['role'];
        if (is_array($roles)) {
            return in_array($userRole, $roles, true); // Use strict comparison
        } else {
            return $userRole === $roles; // Use strict comparison
        }
    }

    /**
     * Check if the current user has a specific permission.
     * @param string $permission The permission to check.
     * @return bool True if the user has the permission, false otherwise.
     */
    public function hasPermission(string $permission): bool {
        $user = $this->getCurrentUser();
        if (!$user || !isset($user['permissions'])) {
            return false; // Not logged in or no permissions set
        }

        $permissions = $user['permissions'];
        return in_array($permission, $permissions, true); // Use strict comparison
    }
}
