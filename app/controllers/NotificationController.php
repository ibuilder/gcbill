php
<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Models\Notification;
use App\Models\User;

class NotificationController extends \Core\Controller
{
    protected function before()
    {
        if (!Auth::isLoggedIn()) {
            header('Location: /login');
            exit;
        }

        $user = $_SESSION['user'];

        if (!Auth::checkPermission($user, $this->controller, $this->action)) {
            header('Location: /403');
            exit;
        }
    }

    public function index()
    {
        $user = $_SESSION['user'];
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->view('notifications/index.html', ['notifications' => $notifications]);
    }

    public function view($id)
    {
        $user = $_SESSION['user'];
        $notification = Notification::find($id);

        if (!$notification || $notification->user_id != $user->id) {
            header('Location: /404');
            exit;
        }

        $notification->is_read = true;
        $notification->save();

        $this->view('notifications/view.html', ['notification' => $notification]);
    }
}