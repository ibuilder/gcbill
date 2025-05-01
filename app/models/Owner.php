<?php

namespace App\Models;

use App\Database;

class Owner {
    private Database $db;
    private array $fillable = [
        'owner_name', 'primary_contact_name', 'primary_contact_email',
        'primary_contact_phone', 'address_line1', 'address_line2',
        'city', 'state', 'zip_code'
    ];

    public function __construct(Database $db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Find an owner by ID.
     */
    public function findById(int $id): ?array {
        return $this->db->selectOne("SELECT * FROM owners WHERE id = ?", [$id]);
    }

    /**
     * Get all owners (add pagination later).
     */
    public function findAll(string $orderBy = 'owner_name', string $orderDir = 'ASC'): array {
        $allowedOrderBy = ['id', 'owner_name', 'primary_contact_name', 'city', 'state', 'updated_at'];
        $orderBy = in_array($orderBy, $allowedOrderBy) ? $orderBy : 'owner_name';
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        return $this->db->select("SELECT * FROM owners ORDER BY {$orderBy} {$orderDir}");
    }

    /**
     * Get all owners for simple dropdown list (ID and Name).
     */
    public function findAllSimple(): array {
        return $this->db->select("SELECT id, owner_name FROM owners ORDER BY owner_name ASC");
    }

    /**
     * Create a new owner.
     */
    public function create(array $data): string|false {
        $filteredData = $this->filterFillable($data);
        if (empty($filteredData['owner_name'])) { // Basic validation
            error_log("Owner creation failed: Missing owner_name.");
            return false;
        }
        $filteredData = $this->prepareData($filteredData);
        return $this->db->insert('owners', $filteredData);
    }

    /**
     * Update an existing owner.
     */
    public function update(int $id, array $data): int {
        $filteredData = $this->filterFillable($data);
        if (empty($filteredData)) {
            return 0;
        }
        if (empty($filteredData['owner_name'])) { // Basic validation
             error_log("Owner update failed: Missing owner_name.");
             return -1; // Indicate error
        }
        $filteredData = $this->prepareData($filteredData);
        return $this->db->update('owners', $filteredData, 'id = ?', [$id]);
    }

    /**
     * Delete an owner.
     * Note: The foreign key constraint on projects (ON DELETE SET NULL) will handle linked projects.
     */
    public function delete(int $id): int {
        // Check if owner is linked to any projects (optional, FK handles it)
        // $projectCount = $this->db->selectValue("SELECT COUNT(*) FROM projects WHERE owner_id = ?", [$id]);
        // if ($projectCount > 0) {
        //     error_log("Cannot delete owner ID {$id}: Linked to {$projectCount} projects.");
        //     return -1; // Indicate error or prevent deletion
        // }
        return $this->db->delete('owners', 'id = ?', [$id]);
    }

    /**
     * Check if an owner name already exists (for validation).
     */
    public function ownerNameExists(string $ownerName, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) FROM owners WHERE owner_name = ?";
        $params = [$ownerName];
        if ($excludeId !== null) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        return $this->db->selectValue($query, $params) > 0;
    }

    private function filterFillable(array $data): array {
        return array_intersect_key($data, array_flip($this->fillable));
    }

    private function prepareData(array $data): array {
        // Convert empty strings for potentially nullable fields to null if desired
        // Example:
        // foreach ($this->fillable as $key) {
        //     if (isset($data[$key]) && $data[$key] === '') {
        //         // Decide if empty string should be null based on DB schema
        //         // $data[$key] = null;
        //     }
        // }
        return $data;
    }
}