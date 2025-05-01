<?php

namespace App\Models;

class ChangeOrder
{
    private $id;
    private $project_id;
    private $change_order_number;
    private $description;
    private $change_order_date;
    private $status;
    private $created_at;
    private $updated_at;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->project_id = $data['project_id'] ?? null;
        $this->change_order_number = $data['change_order_number'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->change_order_date = $data['change_order_date'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        return null;
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
}