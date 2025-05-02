<?php

namespace App\Models;

// use App\Core\Model;

class UserPermission
{
    protected $table = 'user_permissions';

    /**
     * Get all permissions for a user
     */
    public function getUserPermissions(int $userId): array
    {
       return []; 
        // return $this->findAllBy('user_id', $userId);
    }
}