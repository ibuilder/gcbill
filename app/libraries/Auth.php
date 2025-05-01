<?php

namespace App\Libraries;

use App\Models\UserPermission;
use App\Models\User;

class Auth
{
    public static function checkPermission(object $user, string $controller, string $action): bool
    {
        if (!isset($user->role)) {
            return false;
        }

        $permission = UserPermission::where('controller', $controller)
            ->where('action', $action)
            ->where('role', $user->role)
            ->first();

        return $permission !== null;
    }

    public static function getUserRole(object $user): ?string
    {
        return isset($user->role) ? $user->role : null;
    }

    public static function login(object $user)
    {
        session_start();
        $_SESSION['user_id'] = $user->id;
    }
    
    public static function logout()
    {
        session_start();
        session_unset();
        session_destroy();
    }
    
    public static function isLoggedIn(): bool
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}