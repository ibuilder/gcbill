<?php

namespace App\Models;

use App\Database;

class ChangeOrder
{
    private Database $db;
    // Define fillable fields to prevent mass assignment vulnerabilities
    private array $fillable = [
        'project_id', 'change_order_number', 'description', 'change_order_date', 'status'
    ];

    /**
     * Constructor for the ChangeOrder class.
     * @param Database $db The database instance.
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Magic method to get properties.
     * @param string $property The property name.
     * @return mixed The property value or null if not found.
     */
    
    public function __get($property)
    {   
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        return null;
    }


    /**
     * Magic method to set properties.
     * @param string $property The property name.
     * @param mixed $value The property value.
     * @return void
     */
    public function __set(string $property, $value): void
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }

    /**
     * Find a change order by its ID.
     * @param int $id
     * @return array|null Change order data or null if not found
     */
    public function find(int $id): ?array {
        try{
            $query = "SELECT * FROM change_orders WHERE id = ?";
            return $this->db->selectOne($query, [$id]);
        }catch(Exception $e){
            error_log('Error in ChangeOrder::find: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all change orders for a given project ID.
     * @param int $projectId The project ID.
     * @return array List of change orders
     */
    public function all(int $projectId): array {
        try{
            $query = "SELECT * FROM change_orders WHERE project_id = ?";
            return $this->db->select($query, [$projectId]);
        }catch(Exception $e){
            error_log('Error in ChangeOrder::all: ' . $e->getMessage());
            return [];

        }
    }

    /**
     * Create a new change order.
     * @param array $data Change order data
     * @return string|false Last insert ID or false on failure
     */
    public function insert(array $data): string|false {
        try{
            $filteredData = $this->filterFillable($data);
            $preparedData = $this->prepareData($filteredData);
            return $this->db->insert('change_orders', $preparedData);
        }catch(Exception $e){
            error_log('Error in ChangeOrder::insert: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing change order.
     * 
     * @param int $id Change order ID
     * @param array $data Data to update
     * @return int Number of affected rows or -1 on error
     */
    public function update(int $id, array $data): int {
        try{
            $filteredData = $this->filterFillable($data);
            $preparedData = $this->prepareData($filteredData);
            return $this->db->update('change_orders', $preparedData, 'id = ?', [$id]);
        }catch(Exception $e){
            error_log('Error in ChangeOrder::update: ' . $e->getMessage());
            return -1;
        }
    }

    /**
     * Delete a change order.
     * Delete a change order.
     * @param int $id Change order ID
     * @return int Number of affected rows or -1 on error
     */
    public function remove(int $id): int {
        try{
            return $this->db->delete('change_orders', 'id = ?', [$id]);
        }catch(Exception $e){
            error_log('Error in ChangeOrder::remove: ' . $e->getMessage());
            return -1;
        }
    }

    /**
     * Filter data array to include only fillable fields.
     * @param array $data The data to filter.
     * @return array The filtered data.
     */
    private function filterFillable(array $data): array {
     * Prepare data for DB insertion/update (handle nulls, types).
     */
    private function prepareData(array $data): array {
        // Convert empty strings for numeric/date fields to null
        $nullableDate = ['change_order_date'];

        foreach ($nullableDate as $key) {
            if (isset($data[$key]) && ($data[$key] === '' || $data[$key] === '0000-00-00')) {
                $data[$key] = null;
            }
        }

        return $data;
    }
        return array_intersect_key($data, array_flip($this->fillable));
    }
}