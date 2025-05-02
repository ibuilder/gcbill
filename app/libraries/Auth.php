<?php

namespace App\Libraries;

use App\Models\UserPermission;
use App\Models\User; // Assuming you have a User model that can fetch user details
use App\Database; // Assuming Database class is available for fetching user if needed

class Auth
{
    /**
     * Checks if a user has permission for a specific controller and action based on their role.
     *
     * @param object $user The user object (should contain a 'role' property).
     * @param string $controller The name of the controller.
     * @param string $action The name of the action.
     * @param Database $db Database connection instance.
     * @return bool True if the user has permission, false otherwise.
     */
    public static function checkPermission(object $user, string $controller, string $action, Database $db): bool
    {
        if (!isset($user->role)) {
            error_log("User object does not contain 'role' property in Auth::checkPermission");
            return false;
        }

        // Assuming UserPermission model has a static method 'where' that accepts a Database instance
        // You might need to adjust this based on your actual Model implementation
        $permission = UserPermission::where('controller', $controller, $db)
            ->where('action', $action, $db)
            ->where('role', $user->role, $db)
            ->first(); // Assuming first() returns the model instance or null

        return $permission !== null;
    }

    /**
     * Gets the role of the currently logged-in user.
     *
     * @return string|null The user's role or null if not logged in or role not set.
     */
    public static function getUserRole(): ?string
    {
        $user = self::getUser();
        // Ensure the user object has a 'role' property
        return $user->role ?? null;
    }

    /**
     * Logs a user in by storing their user object in the session.
     *
     * @param object $user The user object to log in.
     * @return void
     */
    public static function login(object $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Store the entire user object or necessary parts
        $_SESSION['user'] = $user;
        // Regenerate session ID for security
        session_regenerate_id(true);
    }

    /**
     * Logs the current user out by destroying the session.
     *
     * @return void
     */
    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Unset all session variables
        $_SESSION = [];

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();
    }

    /**
     * Checks if a user is currently logged in.
     *
     * @return bool True if the user is logged in, false otherwise.
     */
    public static function isLoggedIn(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Check if the 'user' session variable is set and not empty
        // Also verify it's an object as expected by other methods
        return isset($_SESSION['user']) && is_object($_SESSION['user']);
    }

    /**
     * Gets the currently logged-in user object from the session.
     *
     * @return object|null The user object or null if not logged in.
     */
    public static function getUser(): ?object
    {
        if (!self::isLoggedIn()) {
            return null;
        }
        return $_SESSION['user'];
    }

    /**
     * Gets the ID of the currently logged-in user.
     *
     * @return int|null The user ID or null if not logged in or ID not set.
     */
    public static function getUserId(): ?int
    {
         $user = self::getUser();
         // Assuming the user object has an 'id' property
         // Ensure the id property exists and is numeric
         return (isset($user->id) && is_numeric($user->id)) ? (int)$user->id : null;
    }
}