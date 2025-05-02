<?php

<?php

namespace App\Models; // Corrected namespace

use App\Database;
class Billing { // Corrected class name
    private Database $db;
    private array $fillable = [
        'project_id', 'billing_number', 'period_start_date', 'period_end_date',
        'billing_date', 'status', 'notes', 'retainage_rate'
    ];

    public function __construct(Database $db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    public function findById(int $id): ?array {
        // Optionally join project info
        $query = "SELECT b.*, p.project_name, p.project_number, p.contract_amount
                  FROM billings b
                  JOIN projects p ON b.project_id = p.id
                  WHERE b.id = ?";
        return $this->db->selectOne($query, [$id]);
    }

    public function findByProjectId(int $projectId, string $orderBy = 'billing_number', string $orderDir = 'DESC'): array {
        $allowedOrderBy = ['id', 'billing_number', 'period_end_date', 'billing_date', 'status', 'updated_at'];
        $orderBy = in_array($orderBy, $allowedOrderBy) ? $orderBy : 'billing_number';
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        $query = "SELECT * FROM billings
                  WHERE project_id = ?
                  ORDER BY {$orderBy} {$orderDir}";
        return $this->db->select($query, [$projectId]);
    }

    /**
     * Get the next billing number for a project.
     */
    public function getNextBillingNumber(int $projectId): int {
        $max = $this->db->selectValue("SELECT MAX(billing_number) FROM billings WHERE project_id = ?", [$projectId]);
        return ($max ?? 0) + 1;
    }

    /**
     * Get the most recent completed billing for a project (if any).
     * Useful for calculating previous values. Status check might be needed.
     */
    public function findLatestCompletedByProjectId(int $projectId): ?array {
         $query = "SELECT * FROM billings
                   WHERE project_id = ?
                   -- AND status IN ('approved', 'paid') -- Adjust status based on workflow
                   ORDER BY billing_number DESC
                   LIMIT 1";
         return $this->db->selectOne($query, [$projectId]);
    }

    public function create(array $data): string|false {
        $filteredData = $this->filterFillable($data);
        // Basic validation
        if (empty($filteredData['project_id']) || empty($filteredData['billing_number']) || empty($filteredData['period_end_date']) || empty($filteredData['billing_date'])) {
            error_log("Billing creation failed: Missing required fields.");
            return false;
        }
        // Ensure billing number is unique for the project
        if ($this->billingNumberExists($filteredData['project_id'], $filteredData['billing_number'])) {
             error_log("Billing creation failed: Billing number {$filteredData['billing_number']} already exists for project {$filteredData['project_id']}.");
             return false;
        }

        $filteredData = $this->prepareData($filteredData);
        return $this->db->insert('billings', $filteredData);
    }

    public function update(int $id, array $data): int {
        $filteredData = $this->filterFillable($data);
        if (empty($filteredData)) return 0;

        // Prevent changing project_id or billing_number easily after creation?
        unset($filteredData['project_id'], $filteredData['billing_number']);

        $filteredData = $this->prepareData($filteredData);
        return $this->db->update('billings', $filteredData, 'id = ?', [$id]);
    }

    /**
     * Delete a billing. This will also cascade delete billing_details.
     * Use with caution, especially for submitted/approved billings.
     */
    public function delete(int $id): int {
        // Add checks here? Prevent deletion based on status?
        // $billing = $this->findById($id);
        // if ($billing && !in_array($billing['status'], ['draft'])) {
        //     error_log("Cannot delete billing ID {$id}: Status is '{$billing['status']}'.");
        //     return -1;
        // }
        return $this->db->delete('billings', 'id = ?', [$id]);
    }

    public function billingNumberExists(int $projectId, int $billingNumber, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) FROM billings WHERE project_id = ? AND billing_number = ?";
        $params = [$projectId, $billingNumber];
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
        $nullableDate = ['period_start_date'];
        $nullableDecimal = ['retainage_rate'];

        foreach ($nullableDate as $key) {
            if (isset($data[$key]) && ($data[$key] === '' || $data[$key] === '0000-00-00')) {
                $data[$key] = null;
            }
        }
         foreach ($nullableDecimal as $key) {
            if (isset($data[$key]) && $data[$key] === '') {
                $data[$key] = null;
            } elseif (isset($data[$key])) {
                $data[$key] = (float) $data[$key]; // Ensure float
            }
        }
        // Ensure required dates are valid or handle errors
        // Ensure status is valid if provided
        if (isset($data['status']) && !in_array($data['status'], ['draft', 'submitted', 'approved', 'paid'])) {
            $data['status'] = 'draft'; // Default to draft if invalid
        }

        return $data;
    }
}