<?php

namespace App\Models;

class ChangeOrderItem
{
    private $id;
    private $change_order_id;
    private $description;
    private $amount;
    private $created_at;
    private $updated_at;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->change_order_id = $data['change_order_id'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->amount = $data['amount'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
}