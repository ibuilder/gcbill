<?php
// filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\app\Models\Staff.php
<?php

namespace App\Models;

use App\Database;

class Staff {
    private Database $db;
    private array $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'position_id',
        'hire_date', 'is_active', 'user_id'
    ];

    public function __construct(Database $db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Find a staff member by ID, joining related tables.
     */
    public function findById(int $id): ?array {
        $query = "SELECT s.*, sp.position_title, u.username
                  FROM staff s
                  LEFT JOIN staff_positions sp ON s.position_id = sp.id
                  LEFT JOIN users u ON s.user_id = u.id
                  WHERE s.id = ?";
        return $this->db->selectOne($query, [$id]);
    }

    /**
     * Get all staff members (add pagination later).
     */
    public function findAll(string $orderBy = 'last_name', string $orderDir = 'ASC'): array {
        $allowedOrderBy = ['id', 'first_name', 'last_name', 'email', 'position_title', 'hire_date', 'is_active', 'username'];
        // Adjust validation if sorting by joined fields
        $orderBy = in_array($orderBy, $allowedOrderBy) ? $orderBy : 's.last_name'; // Prefix with alias
        if ($orderBy === 'position_title') $orderBy = 'sp.position_title';
        if ($orderBy === 'username') $orderBy = 'u.username';

        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        $query = "SELECT s.id, s.first_name, s.last_name, s.email, s.phone, s.is_active,
                         sp.position_title, u.username
                  FROM staff s
                  LEFT JOIN staff_positions sp ON s.position_id = sp.id
                  LEFT JOIN users u ON s.user_id = u.id
                  ORDER BY {$orderBy} {$orderDir}, s.first_name {$orderDir}"; // Secondary sort
        return $this->db->select($query);
    }

    /**
     * Get staff members for simple dropdown list (ID and Name).
     */
    public function findAllSimple(): array {
         return $this->db->select("SELECT id, CONCAT(first_name, ' ', last_name) as full_name FROM staff WHERE is_active = 1 ORDER BY last_name, first_name");
    }

    /**
     * Create a new staff member.
     */
    public function create(array $data): string|false {
        $filteredData = $this->filterFillable($data);
        if (empty($filteredData['first_name']) || empty($filteredData['last_name'])) {
            return false; // Required fields
        }
        $filteredData = $this->prepareData($filteredData);
        return $this->db->insert('staff', $filteredData);
    }

    /**
     * Update an existing staff member.
     */
    public function update(int $id, array $data): int {
        $filteredData = $this->filterFillable($data);
        if (empty($filteredData)) return 0;
        if (empty($filteredData['first_name']) || empty($filteredData['last_name'])) {
             return -1; // Required fields
        }
        $filteredData = $this->prepareData($filteredData);
        return $this->db->update('staff', $filteredData, 'id = ?', [$id]);
    }

    /**
     * Delete a staff member.
     * Consider implications for time entries, project assignments etc.
     * Foreign keys should handle basic integrity (e.g., ON DELETE SET NULL or CASCADE).
     */
    public function delete(int $id): int {
        // Check for related records if necessary before deletion
        return $this->db->delete('staff', 'id = ?', [$id]);
    }

    /**
     * Check if an email already exists (for validation).
     */
    public function emailExists(string $email, ?int $excludeId = null): bool {
        if (empty($email)) return false; // Don't check empty emails
        $query = "SELECT COUNT(*) FROM staff WHERE email = ?";
        $params = [$email];
        if ($excludeId !== null) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        return $this->db->selectValue($query, $params) > 0;
    }

    /**
     * Check if a user ID is already linked to another staff member.
     */
     public function userIdLinked(int $userId, ?int $excludeStaffId = null): bool {
         if (empty($userId)) return false;
         $query = "SELECT COUNT(*) FROM staff WHERE user_id = ?";
         $params = [$userId];
         if ($excludeStaffId !== null) {
             $query .= " AND id != ?";
             $params[] = $excludeStaffId;
         }
         return $this->db->selectValue($query, $params) > 0;
     }

    private function filterFillable(array $data): array {
        return array_intersect_key($data, array_flip($this->fillable));
    }

    private function prepareData(array $data): array {
        // Handle nulls for optional fields
        $nullable = ['email', 'phone', 'position_id', 'hire_date', 'user_id'];
        foreach ($nullable as $key) {
            if (isset($data[$key]) && ($data[$key] === '' || $data[$key] === '0')) { // Treat '0' as empty for FKs
                $data[$key] = null;
            }
        }
        if (isset($data['hire_date']) && $data['hire_date'] === '0000-00-00') {
             $data['hire_date'] = null;
        }
        // Ensure boolean is stored correctly
        $data['is_active'] = isset($data['is_active']) ? 1 : 0;

        return $data;
    }
}