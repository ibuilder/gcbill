<?php

namespace App\Models;

use App\Database;

class Sov {
    private Database $db;
    private array $fillable = ['project_id', 'item_number', 'description', 'scheduled_value'];

    public function __construct(Database $db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Find an SOV item by its ID.
     */
    public function findById(int $id): ?array {
        return $this->db->selectOne("SELECT * FROM schedule_of_values WHERE id = ?", [$id]);
    }

    /**
     * Find all SOV items for a specific project.
     */
    public function findByProjectId(int $projectId, string $orderBy = 'item_number', string $orderDir = 'ASC'): array {
        // Basic validation for order columns/direction
        $allowedOrderBy = ['id', 'item_number', 'description', 'scheduled_value', 'updated_at'];
        $orderBy = in_array($orderBy, $allowedOrderBy) ? $orderBy : 'item_number';
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        $query = "SELECT * FROM schedule_of_values
                  WHERE project_id = ?
                  ORDER BY {$orderBy} {$orderDir}";
        return $this->db->select($query, [$projectId]);
    }

    /**
     * Create a new SOV item.
     */
    public function create(array $data): string|false {
        $filteredData = $this->filterFillable($data);
        // Basic validation
        if (empty($filteredData['project_id']) || !isset($filteredData['item_number']) || empty($filteredData['description']) || !isset($filteredData['scheduled_value'])) {
            error_log("SOV creation failed: Missing required fields.");
            return false;
        }
        if ($this->itemNumberExists($filteredData['project_id'], $filteredData['item_number'])) {
             error_log("SOV creation failed: Item number already exists for this project.");
             return false;
        }
        $filteredData = $this->prepareData($filteredData);
        return $this->db->insert('schedule_of_values', $filteredData);
    }

    /**
     * Update an existing SOV item.
     */
    public function update(int $id, array $data): int {
        $filteredData = $this->filterFillable($data);
        if (empty($filteredData)) return 0;

        // Fetch original project_id if not provided in update data
        $originalProjectId = $filteredData['project_id'] ?? $this->findById($id)['project_id'] ?? null;
        if (!$originalProjectId) return -1; // Cannot update without project context

        // Check for item number uniqueness if it's being changed
        if (isset($filteredData['item_number'])) {
             if ($this->itemNumberExists($originalProjectId, $filteredData['item_number'], $id)) {
                 error_log("SOV update failed: Item number already exists for this project.");
                 return -1;
             }
        }

        $filteredData = $this->prepareData($filteredData);
        return $this->db->update('schedule_of_values', $filteredData, 'id = ?', [$id]);
    }

    /**
     * Delete an SOV item.
     * Prevents deletion if the item has been used in any billing details.
     */
    public function delete(int $id): int {
        // Check if this SOV item exists in any `billing_details` record
        $billingCount = $this->db->selectValue(
            "SELECT COUNT(*) FROM billing_details WHERE sov_item_id = ?",
            [$id]
        );

        if ($billingCount > 0) {
            error_log("Cannot delete SOV item ID {$id}: It has been used in {$billingCount} billing(s).");
            return -1; // Indicate error: Deletion prevented
        }

        // Proceed with deletion if not used in billings
        return $this->db->delete('schedule_of_values', 'id = ?', [$id]);
    }

    /**
     * Calculate the total scheduled value for a project's SOV.
     */
    public function getTotalScheduledValue(int $projectId): float {
        $result = $this->db->selectValue(
            "SELECT SUM(scheduled_value) FROM schedule_of_values WHERE project_id = ?",
            [$projectId]
        );
        return (float) ($result ?? 0.0);
    }

    /**
     * Check if an item number already exists for a given project.
     */
    public function itemNumberExists(int $projectId, string $itemNumber, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) FROM schedule_of_values WHERE project_id = ? AND item_number = ?";
        $params = [$projectId, $itemNumber];
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
        // Ensure numeric values are correctly typed or null
        if (isset($data['scheduled_value']) && $data['scheduled_value'] === '') {
            $data['scheduled_value'] = 0.00; // Default to 0 if empty? Or null? Depends on requirements.
        } elseif (isset($data['scheduled_value'])) {
            $data['scheduled_value'] = (float) $data['scheduled_value'];
        }
        // Ensure project_id is int
        if (isset($data['project_id'])) {
             $data['project_id'] = (int) $data['project_id'];
        }
        return $data;
    }
}