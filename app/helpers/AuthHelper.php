<?php

namespace App\Helpers;

use App\Database;
use App\Models\User; // Use the User model

class AuthHelper {
    private Database $db;
    private User $userModel;
    private string $sessionKey = 'user'; // Key to store user info in session

    public function __construct() {
        $this->db = Database::getInstance();
        $this->userModel = new User($this->db); // Pass DB instance to model

        // Ensure session is started (might be redundant if started elsewhere)
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
    public function login(string $identifier, string $password): bool {
        $user = $this->userModel->findByIdentifier($identifier);

        if ($user && $user['is_active'] && password_verify($password, $user['password_hash'])) {
            // Password matches and user is active
            $this->setSession($user);
            // Update last login timestamp (optional)
            $this->userModel->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);
            return true;
        }

        return false; // Login failed
    }

    /**
     * Log the current user out.
     */
    public function logout(): void {
        unset($_SESSION[$this->sessionKey]);        
        // Optionally destroy the entire session
        // session_destroy();
    }

    /**
     * Check if there is a user currently logged in.
     * @return bool
     */
    public function isLoggedIn(): bool {
        return isset($_SESSION[$this->sessionKey]) && !empty($_SESSION[$this->sessionKey]);
    }

    public function getCurrentUser(): array
    {
        return $_SESSION[$this->sessionKey] ?? [];
    }

    /**
     * Get the ID of the currently logged-in user.
     * @return int|null User ID or null if not logged in
     */
    public function getUserId(): ?int {
    }

    /**
     * Set the user session.
     * @param int $userId
     */
    private function setSession(int $userId): void {
         // Regenerate session ID for security upon login
         session_regenerate_id(true);
         $_SESSION[$this->sessionKey] = $userId;
    }

    /**
     * Hash a password using configured algorithm.
     * @param string $password
     * @return string Hashed password
     */
    public function hashPassword(string $password): string {
        global $config;
        $algo = $config['security']['password_hash_algo'] ?? PASSWORD_DEFAULT;
        $options = $config['security']['password_hash_options'] ?? [];
        return password_hash($password, $algo, $options);
    }

    // Add methods for permission checks (e.g., checkRole, hasPermission)
    public function checkRole(string|array $roles): bool {
         $user = $this->getCurrentUser();
         if (!$user) return false;

         if (is_array($roles)) {
             return in_array($user['role'], $roles);
         } else {
             return $user['role'] === $roles;
         }
    }
}