<?php

namespace AppControllers;

use AppLibrariesAuth;
use AppDatabase;
use AppModelsNotification;

class NotificationController extends BaseController
{
    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    public function index()
    {
        $user = $_SESSION['user'];        
        $notificationModel = new Notification($this->db);
        $notifications = $notificationModel->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->all();

        return $this->view('notifications/index', ['notifications' => $notifications]);
    }

        $this->view('notifications/index.html', ['notifications' => $notifications]);
    }

    public function view($id)
    {
      $user = $_SESSION['user'];
      $notificationModel = new Notification($this->db);
      $notification = $notificationModel->find($id);

      if (!$notification || $notification->user_id != $user->id) {
          header('Location: /404');
          exit;
      }
      $notificationModel->update($id,['is_read' => true]);
      return $this->view('notifications/view', ['notification' => $notification]);
        
    }
}