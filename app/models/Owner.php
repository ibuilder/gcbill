<?php

namespace App\Models; // Correct namespace

use App\Database;

class Owner { // Correct class name
    private Database $db;
    private array $fillable = [
        'owner_name', 'primary_contact_name', 'primary_contact_email',
        'primary_contact_phone', 'address_line1', 'address_line2',
        'city', 'state', 'zip_code'
    ];

    /**
     * Constructor for the Owner class.
     * @param Database|null $db The database instance.
     */
    public function __construct(Database $db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Find an owner by ID. 
     */
    /**
     * @param int $id The owner ID.*/
    public function find(int $id): ?array {
        try{
            return $this->db->selectOne("SELECT * FROM owners WHERE id = ?", [$id]);
        }catch(Exception $e){
            error_log('Error in Owner::find: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all owners (add pagination later). 
     * @param string $orderBy The column to order by.
     * @param string $orderDir The order direction (ASC or DESC).
     * @return array The list of owners.
     */
    public function all(string $orderBy = 'owner_name', string $orderDir = 'ASC'): array {
        try{
            $allowedOrderBy = ['id', 'owner_name', 'primary_contact_name', 'city', 'state', 'updated_at'];
            $orderBy = in_array($orderBy, $allowedOrderBy) ? $orderBy : 'owner_name';
            $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
            return $this->db->select("SELECT * FROM owners ORDER BY {$orderBy} {$orderDir}");
        }catch(Exception $e){
            error_log('Error in Owner::all: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all owners for simple dropdown list (ID and Name).
     * @return array The list of owners (ID and Name).
     */
    public function findAllSimple(): array {
        return $this->db->select("SELECT id, owner_name FROM owners ORDER BY owner_name ASC");
    }

    public function insert(array $data): string|false {
        try{
          /**
           * Create a new owner.
           * @param array $data The owner data.
           * @return string|false The last insert ID or false on failure.*/
            $filteredData = $this->filterFillable($data);
            if (empty($filteredData['owner_name'])) { // Basic validation
                error_log("Owner creation failed: Missing owner_name.");
                return false;
            }
            $preparedData = $this->prepareData($filteredData);
            return $this->db->insert('owners', $preparedData);
        } catch(\Exception $e){
            error_log('Error in Owner::insert: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing owner. 
     * @param int $id The owner ID.
     * @param array $data The owner data.
     * @return int The number of affected rows or -1 on error.

     */
    public function update(int $id, array $data): int {
        try{
            $filteredData = $this->filterFillable($data);
            if (empty($filteredData)) {
                return 0;
            }
            if (empty($filteredData['owner_name'])) { // Basic validation
                 error_log("Owner update failed: Missing owner_name.");
                 return -1; // Indicate error
            }
            $preparedData = $this->prepareData($filteredData);
            return $this->db->update('owners', $preparedData, 'id = ?', [$id]);
        } catch(\Exception $e){
            error_log('Error in Owner::update: ' . $e->getMessage());
            return -1;
        }
    }

    /**
     * Delete an owner.
     * @param int $id The owner ID.
     * @return int The number of affected rows or -1 on error.
     * Note: The foreign key constraint on projects (ON DELETE SET NULL) will handle linked projects.
     */
    public function remove(int $id): int {
        try {
            return $this->db->delete('owners', 'id = ?', [$id]);
        } catch(\Exception $e){
            error_log('Error in Owner::remove: ' . $e->getMessage());
            return -1;
        }
    }

    /**
     * Check if an owner name already exists (for validation).
     * @param string $ownerName The owner name to check.
     * @param int|null $excludeId Optional. The ID of an owner to exclude from the check.
     * @return bool True if the owner name exists, false otherwise.
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

    /**
     * Filter data array to include only fillable fields.
     * @param array $data The data to filter.
     * @return array The filtered data.
     */
    private function filterFillable(array $data): array {
        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Prepare data for DB insertion/update (handle nulls, types).
     * @param array $data The data to prepare. @return array The prepared data.*/
    private function prepareData(array $data): array {
        // Convert empty strings for potentially nullable fields to null if desired
        $nullableFields = ['primary_contact_phone', 'address_line1', 'address_line2', 'city', 'state', 'zip_code'];

        foreach ($nullableFields as $key) {
            if (isset($data[$key]) && $data[$key] === '') {
                $data[$key] = null;
            }
        }
        return $data;
    }
}