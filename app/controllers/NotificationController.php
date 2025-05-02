<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Database;
use App\Models\Notification;

class NotificationController extends \App\Controllers\BaseController
{
    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    public function index()
    {
        $user = $_SESSION['user'];
        $notificationModel = new Notification($this->db);
        $notifications = $notificationModel->select("SELECT * FROM `notifications` WHERE user_id = ? ORDER BY created_at DESC", [$user->id]);
        return $this->view('notifications/index', ['notifications' => $notifications]);
    }


    public function view($id)
    {
     $user = $_SESSION['user'];
     $notificationModel = new Notification($this->db);
     $notification = $notificationModel->selectOne("SELECT * FROM `notifications` WHERE id = ? ",[$id]);

     if (!$notification || $notification['user_id'] != $user->id) {
            header('Location: /404');
            exit;
        }
        $notificationModel->update("notifications", ['is_read' => true],"id = ?",[$id]);
        return $this->view('notifications/view', ['notification' => $notification]);

    }
}