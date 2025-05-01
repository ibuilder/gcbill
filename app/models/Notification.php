<?php

namespace App\Models;

use App\Core\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    public function send(int $userId, string $message)
    {
        $data = [
            'user_id' => $userId,
            'message' => $message,
            'is_read' => false,
        ];
        return $this->create($data, 'notifications');
    }
}
    }
}