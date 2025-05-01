<?php

namespace App\Models;

use App\Database; // Use the Database class

class User {
    private Database $db;

    public function __construct(Database $db = null) {
        $this->db = $db ?? Database::getInstance(); // Use injected DB or singleton
    }

    /**
     * Find a user by their ID.
     * @param int $id
     * @return array|null User data or null if not found
     */
    public function findById(int $id): ?array {
        return $this->db->selectOne("SELECT id, username, email, first_name, last_name, role, is_active FROM users WHERE id = ?", [$id]);
    }

    /**
     * Find a user by their username or email (for login).
     * Includes password hash for verification.
     * @param string $identifier Username or Email
     * @return array|null User data or null if not found
     */
    public function findByIdentifier(string $identifier): ?array {
        return $this->db->selectOne(
            "SELECT id, username, email, password_hash, role, is_active FROM users WHERE username = :identifier OR email = :identifier",
            ['identifier' => $identifier]
        );
    }

    /**
     * Create a new user.
     * @param array $data User data (username, email, password_hash, role, etc.)
     * @return string|false Last insert ID or false on failure
     */
    public function create(array $data): string|false {
        // Add validation, ensure required fields are present
        if (empty($data['username']) || empty($data['email']) || empty($data['password_hash'])) {
            return false;
        }
        // Hash password before calling this method!
        return $this->db->insert('users', $data);
    }

    // Add methods for update, delete, password reset token handling, etc.

    /**
     * Update user details.
     * @param int $id User ID
     * @param array $data Data to update
     * @return int Number of affected rows or -1 on error
     */
     public function update(int $id, array $data): int {
         // Prevent updating primary key or potentially sensitive fields directly if needed
         unset($data['id']);
         if (empty($data)) return 0;
         return $this->db->update('users', $data, 'id = ?', [$id]);
     }

    /**
     * Get all users for simple dropdown list (ID and Username).
     * Optionally filter by active status.
     * @param bool $activeOnly Only return active users
     * @return array
     */
    public function findAllSimple(bool $activeOnly = true): array {
        $query = "SELECT id, username FROM users";
        if ($activeOnly) {
            $query .= " WHERE is_active = 1";
        }
        $query .= " ORDER BY username ASC";
        return $this->db->select($query);
    }
}