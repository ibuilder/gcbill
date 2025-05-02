<?php

namespace App\Models;

use App\Database;

class Project {
    private Database $db;

    // Define fillable fields to prevent mass assignment vulnerabilities
    private array $fillable = [
        'project_number', 'project_name', 'address_line1', 'address_line2',
        'city', 'state', 'zip_code', 'start_date', 'completion_date',
        'contract_amount', 'gmp_amount', 'gc_fee_percentage', 'retainage_percentage',
        'status', 'owner_id'
    ];

    public function __construct(Database $db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Find a project by its ID.
     * @param int $id
     * @return array|null Project data or null if not found
     */
    public function find(int $id): ?array {
        // Join with owners table to get owner name
        $query = "SELECT p.*, o.owner_name
                  FROM projects p
                  LEFT JOIN owners o ON p.owner_id = o.id
                  WHERE p.id = ?";
        return $this->db->selectOne($query, [$id]);
    }

    /**
     * Get all projects (add pagination later).
     * @param string $orderBy Column to order by
     * @param string $orderDir Direction (ASC or DESC)
     * @return array List of projects
     */
    public function all(string $orderBy = 'project_number', string $orderDir = 'ASC'): array {
        // Basic validation for order columns/direction
        $allowedOrderBy = ['id', 'project_number', 'project_name', 'start_date', 'status', 'updated_at'];
        $orderBy = in_array($orderBy, $allowedOrderBy) ? $orderBy : 'project_number';
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        $query = "SELECT p.id, p.project_number, p.project_name, p.status, p.start_date, o.owner_name
                  FROM projects p
                  LEFT JOIN owners o ON p.owner_id = o.id
                  ORDER BY {$orderBy} {$orderDir}";
        return $this->db->select($query);
    }

    /**
     * Create a new project.
     * @param array $data Project data
     * @return string|false Last insert ID or false on failure
     */
    public function insert(array $data): string|false {
        $filteredData = $this->filterFillable($data);
        // Add validation here or in the controller
        if (empty($filteredData['project_number']) || empty($filteredData['project_name'])) {
            // Basic required field check
            error_log("Project creation failed: Missing required fields (number or name).");
            return false;
        }
        if ($this->projectNumberExists($filteredData['project_number'])) {
            error_log("Project creation failed: Project number already exists.");
            return false;
        }
        // Set default status if not provided
        $filteredData['status'] = $filteredData['status'] ?? 'planning';
        // Handle empty dates/numbers
        $filteredData = $this->prepareData($filteredData);

        return $this->db->insert('projects', $filteredData);
    }

    /**
     * Update an existing project.
     * @param int $id Project ID
     * @param array $data Data to update
     * @return int Number of affected rows or -1 on error
     */
    public function update(int $id, array $data): int {
        $filteredData = $this->filterFillable($data);
        if (empty($filteredData)) {
            return 0;
        }
        // Handle empty dates/numbers
        $filteredData = $this->prepareData($filteredData);

        return $this->db->update('projects', $filteredData, 'id = ?', [$id]);
    }

    /**
     * Delete a project.
     * @param int $id Project ID
     * @return int Number of affected rows or -1 on error
     */
    public function remove(int $id): int {
        // Consider related data (billings, SOV etc.) - cascade delete or prevent deletion?
        // Foreign key constraints handle some of this (ON DELETE CASCADE/SET NULL)
        return $this->db->delete('projects', 'id = ?', [$id]);
    }

    /**
     * Check if a project number already exists (for validation).
     * @param string $projectNumber
     * @param int|null $excludeId ID to exclude (when updating)
     * @return bool
     */
    public function projectNumberExists(string $projectNumber, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) FROM projects WHERE project_number = ?";
        $params = [$projectNumber];
        if ($excludeId !== null) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        return $this->db->selectValue($query, $params) > 0;
    }

    /**
     * Filter data array to include only fillable fields.
     */
    private function filterFillable(array $data): array {
        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Prepare data for DB insertion/update (handle nulls, types).
     */
    private function prepareData(array $data): array {
        // Convert empty strings for numeric/date fields to null
        $nullableNumeric = ['contract_amount', 'gmp_amount', 'gc_fee_percentage', 'retainage_percentage', 'owner_id'];
        $nullableDate = ['start_date', 'completion_date'];

        foreach ($nullableNumeric as $key) {
            if (isset($data[$key]) && $data[$key] === '') {
                $data[$key] = null;
            }
        }
         foreach ($nullableDate as $key) {
            if (isset($data[$key]) && ($data[$key] === '' || $data[$key] === '0000-00-00')) {
                $data[$key] = null;
            }
        }
        // Ensure percentages are stored correctly
        // if (isset($data['gc_fee_percentage'])) $data['gc_fee_percentage'] = $data['gc_fee_percentage'] / 100; // Store as decimal? Or keep as %? Decide convention.
        // if (isset($data['retainage_percentage'])) $data['retainage_percentage'] = $data['retainage_percentage'] / 100;

        return $data;
    }
}