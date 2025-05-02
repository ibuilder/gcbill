<?php

namespace App\Models;

use App\Database;
use PDO;

class ChangeOrder extends Model
{
    protected static string $tableName = 'change_orders'; // Assuming table name

    // Define expected properties (optional)
    public ?int $id = null;
    public ?int $project_id = null;
    public ?string $co_number = null;
    public ?string $description = null;
    public ?float $amount = null;
    public ?string $status = null; // e.g., Pending, Approved, Rejected
    public ?string $date_approved = null;
    // Add other relevant fields (e.g., requested_by, date_submitted)
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * Get approved change orders for a project up to a specific date.
     *
     * @param int $projectId
     * @param string $throughDate YYYY-MM-DD format
     * @param Database $db
     * @return array Array of ChangeOrder model instances.
     */
    public static function getApprovedChangeOrdersThroughDate(int $projectId, string $throughDate, Database $db): array
    {
        return static::query($db)
            ->where('project_id', '=', $projectId)
            ->where('status', '=', 'Approved')
            ->where('date_approved', '<=', $throughDate) // Assumes date_approved stores the approval date
            ->orderBy('co_number', 'ASC') // Or order by date_approved
            ->get();
    }

    // Override save to handle timestamps automatically
    public function save(): bool
    {
        $now = date('Y-m-d H:i:s');
        if (!isset($this->attributes[static::$primaryKey]) || empty($this->attributes[static::$primaryKey])) {
            // Inserting
            if (!isset($this->attributes['created_at'])) {
                $this->setAttribute('created_at', $now);
            }
        }
        // Always set updated_at on save
        $this->setAttribute('updated_at', $now);

        return parent::save();
    }
}