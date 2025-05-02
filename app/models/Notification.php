<?php

namespace App\Models;




class Notification
{
   public function send(int $userId, string $message)
    {
        // $data = [
        //     'user_id' => $userId,
        //     'message' => $message,
        //     'is_read' => false,
        // ];
        // return $this->create($data, 'notifications');
    }
}
