<?php

namespace App\Controllers;

use App\Database;
use App\Models\Notification;
// Auth is likely handled by BaseController

class NotificationController extends BaseController
{
    protected string $controllerName = 'Notification'; // For permissions

    private Notification $notificationModel;

    public function __construct(Database $db, array $config = [])
    {
        parent::__construct($db, $config);
        // Auth check handled by BaseController or requirePermission()
        $this->notificationModel = new Notification($this->db);
    }

    /**
     * List notifications for the current user.
     */
    public function index(): void
    {
        $this->actionName = 'index';
        // No specific permission check needed usually, just logged in (handled by BaseController::before)
        if (!$this->currentUser) {
             $this->redirect('/login'); // Should be caught by BaseController::before anyway
             return;
        }

        $userId = $this->currentUser['id'];
        $notifications = $this->notificationModel->findByUserId($userId); // Use model method

        $this->render('notifications/index', [ // Use .php
            'pageTitle' => 'Notifications',
            'activeNav' => 'notifications', // Define if needed
            'notifications' => $notifications
        ]);
    }

    /**
     * View a specific notification and mark it as read.
     */
    public function view(int $id): void
    {
        $this->actionName = 'view';
        if (!$this->currentUser) {
             $this->redirect('/login');
             return;
        }

        $userId = $this->currentUser['id'];
        $notification = $this->notificationModel->findById($id);

        // Check if notification exists and belongs to the current user
        if (!$notification || $notification['user_id'] != $userId) {
            $this->setFlashMessage('error', 'Notification not found or access denied.');
            $this->redirect('/notifications'); // Redirect to list
            return;
        }

        // Mark as read if not already read
        if (!$notification['is_read']) {
            $this->notificationModel->markAsRead($id);
            // Optionally update the notification array for the view
            $notification['is_read'] = 1;
        }

        $this->render('notifications/view', [ // Use .php
            'pageTitle' => 'View Notification',
            'activeNav' => 'notifications',
            'notification' => $notification
        ]);
    }

    /**
     * Mark all notifications as read for the current user (AJAX example).
     */
    public function markAllRead(): void
    {
         $this->actionName = 'markAllRead';
         if (!$this->currentUser) {
             $this->jsonResponse(['success' => false, 'message' => 'Authentication required.'], 401);
             return;
         }
         // No specific permission usually needed beyond being logged in

         // Optional CSRF check if triggered by a POST request
         // if (!$this->checkCsrfAjax()) return;

         $userId = $this->currentUser['id'];
         $updatedCount = $this->notificationModel->markAllAsReadForUser($userId);

         if ($updatedCount >= 0) {
             $this->jsonResponse(['success' => true, 'message' => "Marked {$updatedCount} notifications as read."]);
         } else {
             $this->jsonResponse(['success' => false, 'message' => 'Failed to mark notifications as read.'], 500);
         }
    }

     /**
     * Delete a specific notification (AJAX example).
     */
    public function delete(int $id): void
    {
         $this->actionName = 'delete';
         if (!$this->currentUser) {
             $this->jsonResponse(['success' => false, 'message' => 'Authentication required.'], 401);
             return;
         }

         // Optional CSRF check if triggered by a POST request
         // if (!$this->checkCsrfAjax()) return;

         $userId = $this->currentUser['id'];
         $notification = $this->notificationModel->findById($id);

         // Check ownership
         if (!$notification || $notification['user_id'] != $userId) {
             $this->jsonResponse(['success' => false, 'message' => 'Notification not found or access denied.'], 404);
             return;
         }

         if ($this->notificationModel->delete($id)) {
             $this->jsonResponse(['success' => true, 'message' => 'Notification deleted.']);
         } else {
             $this->jsonResponse(['success' => false, 'message' => 'Failed to delete notification.'], 500);
         }
    }
}