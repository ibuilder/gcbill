php
<?php

namespace App\Libraries;

use App\Models\UserPermission;
use App\Models\User;

class Auth
{
    public static function checkPermission(string $controller, string $action, string $role): bool
    {
        $permission = UserPermission::where('controller', $controller)
            ->where('action', $action)
            ->where('role', $role)
            ->first();

        return $permission !== null;
    }

    public static function getUserRole(int $userId): ?string
    {
        $user = User::find($userId);
        return $user ? $user->role : null;
    }
}