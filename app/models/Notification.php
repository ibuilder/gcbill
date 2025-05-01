php
<?php

namespace App\Models;

use App\Core\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    public function send($userId, $message)
    {
        $data = [
            'user_id' => $userId,
            'message' => $message,
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return $this->create($data);
    }
}