<?php

namespace App\Models;

use App\Database;

class UserPermission extends Model
{
    protected static string $tableName = 'user_permissions'; // Assuming table name
    // Assuming composite key (role, controller, action) or auto-increment ID? Using 'id'.
    // protected static string $primaryKey = 'id';

    // Define expected properties (optional)
    public ?int $id = null;
    public ?string $role = null; // e.g., 'admin', 'project_manager', 'viewer'
    public ?string $controller = null; // e.g., 'Projects', 'ScheduleOfValues'
    public ?string $action = null; // e.g., 'index', 'create', 'edit', 'delete'

    // Timestamps usually not needed for permission definitions
}