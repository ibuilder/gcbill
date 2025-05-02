<?php

namespace App\Models;

use App\Database;

class BillingPeriod {
    private Database $db;

    // Define fillable fields to prevent mass assignment vulnerabilities
    private array $fillable = [
        'project_id', 'billing_period_number', 'start_date', 'end_date', 'description'
    ];

    public function __construct(Database $db) {
        $this->db = $db;
    }

    /**
     * Find a billing period by its ID.
     * @param int $id
     * @return array|null Billing period data or null if not found
     */
    public function find(int $id): ?array {    
        try{
            $query = "SELECT * FROM billing_periods WHERE id = ?";
            return $this->db->selectOne($query, [$id]);
        }catch(\Exception $e){
            error_log('Error in BillingPeriod::find: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all billing periods for a given project ID.
     * @param int $projectId The project ID.
     * @return array List of billing periods
     */
    public function all(int $projectId): array {
        try{
            $query = "SELECT * FROM billing_periods WHERE project_id = ?";
            return $this->db->select($query, [$projectId]);
        }catch(\Exception $e){
            error_log('Error in BillingPeriod::all: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new billing period.
     * @param array $data Billing period data
     * @return string|false Last insert ID or false on failure
     */
    public function insert(array $data): string|false {
        try{
            $filteredData = $this->filterFillable($data);
            $preparedData = $this->prepareData($filteredData);
            return $this->db->insert('billing_periods', $preparedData);
        }catch(\Exception $e){
            error_log('Error in BillingPeriod::insert: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing billing period.
     * @param int $id Billing period ID
     * @param array $data Data to update
     * @return int Number of affected rows or -1 on error
     */
    public function update(int $id, array $data): int {
        try{
            $filteredData = $this->filterFillable($data);
            $preparedData = $this->prepareData($filteredData);
            return $this->db->update('billing_periods', $preparedData, 'id = ?', [$id]);
        }catch(\Exception $e){
            error_log('Error in BillingPeriod::update: ' . $e->getMessage());
            return -1;
        }
    }

    /**
     * Delete a billing period.
     * @param int $id Billing period ID
     * @return int Number of affected rows or -1 on error
     */
    public function remove(int $id): int {
        try{
            return $this->db->delete('billing_periods', 'id = ?', [$id]);
        }catch(\Exception $e){
            error_log('Error in BillingPeriod::remove: ' . $e->getMessage());
            return -1;
        }
    }

    /**
     * Prepare data for DB insertion/update (handle nulls, types).
     */
    private function prepareData(array $data): array {
        // Convert empty strings for numeric/date fields to null
        $nullableDate = ['start_date', 'end_date'];

        foreach ($nullableDate as $key) {
            if (isset($data[$key]) && ($data[$key] === '' || $data[$key] === '0000-00-00')) {
                $data[$key] = null;
            }
        }

        return $data;
    }
    /**
     * Filter data array to include only fillable fields.
     */
    private function filterFillable(array $data): array {
        return array_intersect_key($data, array_flip($this->fillable));
    }
}