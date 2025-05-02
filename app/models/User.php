<?php

namespace App\Models;

use App\Database;
use PDO;

class User extends Model
{
    protected static string $tableName = 'users'; // Assuming table name is 'users'

    // Define expected properties (optional, for clarity)
    public ?int $id = null;
    public ?string $username = null;
    public ?string $email = null;
    public ?string $password_hash = null;
    public ?string $name = null;
    public ?string $role = null;
    public bool $is_active = true;
    public ?string $last_login_at = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * Find a user by username or email.
     *
     * @param string $identifier Username or email.
     * @return array|null User data as an array or null if not found.
     */
    public function findByIdentifier(string $identifier): ?array
    {
        $sql = "SELECT * FROM " . static::$tableName . " WHERE username = :identifier OR email = :identifier LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['identifier' => $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (\PDOException $e) {
            error_log("Database Error (User::findByIdentifier): " . $e->getMessage());
            return null;
        }
    }

     /**
     * Update specific fields for a user.
     * Note: This bypasses the main 'save' method's attribute tracking.
     * Use carefully or integrate with the main update logic.
     *
     * @param int $id User ID
     * @param array $data Associative array of fields to update.
     * @return bool Success status.
     */
    public function update(int $id, array $data): bool
    {
        if (empty($data)) {
            return true; // Nothing to update
        }

        // Add updated_at timestamp automatically
        if (!isset($data['updated_at'])) {
             $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $setParts = [];
        $bindings = [];
        foreach ($data as $key => $value) {
            $setParts[] = "{$key} = :{$key}";
            $bindings[$key] = $value;
        }
        $setClause = implode(', ', $setParts);
        $sql = "UPDATE " . static::$tableName . " SET {$setClause} WHERE id = :id";
        $bindings['id'] = $id;

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($bindings);
        } catch (\PDOException $e) {
            error_log("Database Error (User::update): " . $e->getMessage());
            return false;
        }
    }

    // Override save to handle timestamps automatically
    public function save(): bool
    {
        $now = date('Y-m-d H:i:s');
        if (!isset($this->attributes[static::$primaryKey]) || empty($this->attributes[static::$primaryKey])) {
            // Inserting
            if (!isset($this->attributes['created_at'])) {
                $this->setAttribute('created_at', $now);
            }
        }
        // Always set updated_at on save
        $this->setAttribute('updated_at', $now);

        return parent::save();
    }
}