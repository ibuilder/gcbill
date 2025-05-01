<?php
// filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\app\Models\StaffPosition.php
<?php

namespace App\Models;

use App\Database;

class StaffPosition {
    private Database $db;
    private array $fillable = ['position_title', 'description', 'default_billing_rate'];

    public function __construct(Database $db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    public function findById(int $id): ?array {
        return $this->db->selectOne("SELECT * FROM staff_positions WHERE id = ?", [$id]);
    }

    public function findAll(string $orderBy = 'position_title', string $orderDir = 'ASC'): array {
        $allowedOrderBy = ['id', 'position_title', 'default_billing_rate', 'updated_at'];
        $orderBy = in_array($orderBy, $allowedOrderBy) ? $orderBy : 'position_title';
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        return $this->db->select("SELECT * FROM staff_positions ORDER BY {$orderBy} {$orderDir}");
    }

    public function create(array $data): string|false {
        $filteredData = $this->filterFillable($data);
        if (empty($filteredData['position_title'])) {
            return false; // Title is required
        }
        $filteredData = $this->prepareData($filteredData);
        return $this->db->insert('staff_positions', $filteredData);
    }

    public function update(int $id, array $data): int {
        $filteredData = $this->filterFillable($data);
        if (empty($filteredData)) return 0;
        if (empty($filteredData['position_title'])) return -1; // Title is required
        $filteredData = $this->prepareData($filteredData);
        return $this->db->update('staff_positions', $filteredData, 'id = ?', [$id]);
    }

    public function delete(int $id): int {
        // Foreign key on staff table is SET NULL, so deleting a position is safe.
        return $this->db->delete('staff_positions', 'id = ?', [$id]);
    }

    public function positionTitleExists(string $title, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) FROM staff_positions WHERE position_title = ?";
        $params = [$title];
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
        if (isset($data['default_billing_rate']) && $data['default_billing_rate'] === '') {
            $data['default_billing_rate'] = null;
        }
        return $data;
    }
}