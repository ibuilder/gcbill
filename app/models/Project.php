<?php

namespace App\Models;

use App\Database;

class Project extends Model
{
    protected static string $tableName = 'projects'; // Assuming table name is 'projects'

    // Define expected properties (optional)
    public ?int $id = null;
    public ?string $name = null;
    public ?string $project_number = null;
    public ?string $address = null;
    public ?string $owner_name = null;
    public ?string $architect_name = null;
    public ?float $contract_amount = null;
    public ?string $contract_date = null;
    public ?float $retainage_percentage = null;
    // Add other relevant project fields
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