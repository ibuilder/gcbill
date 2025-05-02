<?php

namespace App\Models;

use App\Database;

class ApplicationForPayment extends Model
{
    protected static string $tableName = 'applications_for_payment'; // Assuming table name

    // Define expected properties (optional)
    public ?int $id = null;
    public ?int $project_id = null;
    public ?int $application_number = null;
    public ?string $status = null; // e.g., Draft, Submitted, Approved, Paid, Rejected
    public ?string $period_from_date = null;
    public ?string $period_to_date = null;
    public ?string $application_date = null;
    public ?float $retainage_work_rate = null; // Percentage
    public ?float $retainage_stored_rate = null; // Percentage
    public ?float $total_earned_less_retainage = null; // Store calculated value upon approval
    // Add other relevant header fields
    public ?string $created_at = null;
    public ?string $updated_at = null;

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