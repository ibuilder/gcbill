<?php

namespace App\Models;

use App\Database;

class ApplicationForPaymentDetail extends Model
{
    protected static string $tableName = 'application_payment_details'; // Assuming table name
    // Assuming composite key or just auto-increment ID? Using 'id' for simplicity.
    // protected static string $primaryKey = 'id';

    // Define expected properties (optional)
    public ?int $id = null;
    public ?int $application_id = null;
    public ?int $sov_id = null; // Link to ScheduleOfValues line item

    // Input values for the period
    public ?float $work_completed_this_period = 0.0;
    public ?float $materials_stored_this_period = 0.0;

    // Calculated values (stored for historical record and easier querying)
    public ?float $total_work_completed = 0.0; // To Date (Col D)
    public ?float $materials_presently_stored = 0.0; // To Date (Col E)
    public ?float $total_completed_stored = 0.0; // To Date (Col F)
    public ?float $percent_complete = 0.0; // (Col G)
    public ?float $balance_to_finish = 0.0; // (Col H)
    public ?float $retainage = 0.0; // (Col I)

    // Timestamps not usually needed on detail lines unless tracking edits specifically
    // public ?string $created_at = null;
    // public ?string $updated_at = null;

    // Note: No automatic timestamps added here, manage if needed.
}