<?php

namespace App\Models;

use App\Database;

class BillingDetail {
    private Database $db;
    private array $fillable = [
        'billing_id', 'sov_item_id', 'work_completed_this_period', 'materials_stored_this_period'
        // Add 'work_completed_previous', 'materials_stored_previous' if storing them
    ];

    public function __construct(Database $db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    public function findById(int $id): ?array {
        return $this->db->selectOne("SELECT * FROM billing_details WHERE id = ?", [$id]);
    }

    /**
     * Find all detail items for a specific billing, joined with SOV info.
     */
    public function findByBillingId(int $billingId): array {
        $query = "SELECT bd.*, sov.item_number, sov.description, sov.scheduled_value
                  FROM billing_details bd
                  JOIN schedule_of_values sov ON bd.sov_item_id = sov.id
                  WHERE bd.billing_id = ?
                  ORDER BY sov.item_number ASC"; // Order by SOV item number
        return $this->db->select($query, [$billingId]);
    }

    /**
     * Get details for a specific SOV item across all previous billings for a project.
     * Useful for calculating previously billed amounts.
     * Allows filtering by previous billing status.
     */
    public function findPreviousDetailsForSovItem(
        int $projectId,
        int $sovItemId,
        int $currentBillingNumber,
        array $previousStatuses = ['approved', 'paid'] // Default to counting only approved/paid
    ): array {
         $statusPlaceholders = implode(',', array_fill(0, count($previousStatuses), '?'));
         $query = "SELECT bd.*
                   FROM billing_details bd
                   JOIN billings b ON bd.billing_id = b.id
                   WHERE b.project_id = ?
                     AND bd.sov_item_id = ?
                     AND b.billing_number < ?";

         $params = [$projectId, $sovItemId, $currentBillingNumber];

         if (!empty($previousStatuses)) {
             $query .= " AND b.status IN ({$statusPlaceholders})";
             $params = array_merge($params, $previousStatuses);
         }

         $query .= " ORDER BY b.billing_number ASC";

         return $this->db->select($query, $params);
    }

    /**
     * Calculate total previously billed amounts for a specific SOV item based on specified statuses.
     */
    public function getPreviousTotalsForSovItem(
        int $projectId,
        int $sovItemId,
        int $currentBillingNumber,
        array $previousStatuses = ['approved', 'paid'] // Pass status filter down
    ): array {
        // Use the updated findPreviousDetailsForSovItem method
        $previousDetails = $this->findPreviousDetailsForSovItem($projectId, $sovItemId, $currentBillingNumber, $previousStatuses);
        $totals = [
            'work_completed' => 0.0,
            'materials_stored' => 0.0, // Adjust logic if needed based on how stored materials carry over
        ];
        foreach ($previousDetails as $detail) {
            $totals['work_completed'] += (float)($detail['work_completed_this_period'] ?? 0.0);
            // Simple sum of stored materials this period from previous billings.
            // Real-world logic might only take the *last* period's stored value or zero it out once work is done.
            // Keep simple sum for now.
            $totals['materials_stored'] += (float)($detail['materials_stored_this_period'] ?? 0.0);
        }
        return $totals;
    }

    /**
     * Create or update multiple billing details at once (bulk operation).
     * Expects an array of detail data, keyed by sov_item_id.
     */
    public function saveBulk(int $billingId, array $detailsData): bool {
        $this->db->beginTransaction();
        try {
            // Fetch existing details for this billing to know whether to INSERT or UPDATE
            $existingDetailsRaw = $this->db->select("SELECT id, sov_item_id FROM billing_details WHERE billing_id = ?", [$billingId]);
            $existingDetails = [];
            foreach ($existingDetailsRaw as $row) {
                $existingDetails[$row['sov_item_id']] = $row['id'];
            }

            foreach ($detailsData as $sovId => $data) {
                $sovId = (int)$sovId;
                $detailRecord = [
                    'billing_id' => $billingId,
                    'sov_item_id' => $sovId,
                    'work_completed_this_period' => (float)($data['work_completed_this_period'] ?? 0.0),
                    'materials_stored_this_period' => (float)($data['materials_stored_this_period'] ?? 0.0),
                ];
                $detailRecord = $this->prepareData($detailRecord);

                if (isset($existingDetails[$sovId])) {
                    // Update existing detail
                    $this->db->update('billing_details', $detailRecord, 'id = ?', [$existingDetails[$sovId]]);
                } else {
                    // Insert new detail
                    $this->db->insert('billing_details', $detailRecord);
                }
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error saving billing details in bulk: " . $e->getMessage());
            return false;
        }
    }

    // Basic create/update/delete for single items if needed, but bulk is more common
    public function create(array $data): string|false {
         $filteredData = $this->filterFillable($data);
         if (empty($filteredData['billing_id']) || empty($filteredData['sov_item_id'])) return false;
         $filteredData = $this->prepareData($filteredData);
         return $this->db->insert('billing_details', $filteredData);
    }

    public function update(int $id, array $data): int {
         $filteredData = $this->filterFillable($data);
         if (empty($filteredData)) return 0;
         // Prevent changing billing_id or sov_item_id?
         unset($filteredData['billing_id'], $filteredData['sov_item_id']);
         $filteredData = $this->prepareData($filteredData);
         return $this->db->update('billing_details', $filteredData, 'id = ?', [$id]);
    }

    public function delete(int $id): int {
         return $this->db->delete('billing_details', 'id = ?', [$id]);
    }

     /** Delete all details for a specific billing_id */
     public function deleteByBillingId(int $billingId): int {
         return $this->db->delete('billing_details', 'billing_id = ?', [$billingId]);
     }

    private function filterFillable(array $data): array {
        return array_intersect_key($data, array_flip($this->fillable));
    }

    private function prepareData(array $data): array {
        // Ensure numeric values are floats
        $numericKeys = ['work_completed_this_period', 'materials_stored_this_period'];
        foreach ($numericKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = (float) $data[$key];
            } else {
                $data[$key] = 0.0; // Default to 0 if not set
            }
        }
        return $data;
    }
}