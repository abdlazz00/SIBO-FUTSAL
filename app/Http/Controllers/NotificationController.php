<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user's notifications
     */
    public function index()
    {
        $userId = auth()->id();
        $unread = $this->notificationService->getUnread($userId);
        $all = $this->notificationService->getAll($userId, 30);

        return response()->json([
            'success' => true,
            'unread' => $unread,
            'all' => $all
        ]);
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead(int $id)
    {
        $this->notificationService->markRead($id);

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Mark all user's notifications as read
     */
    public function markAllAsRead()
    {
        $userId = auth()->id();
        $this->notificationService->markAllRead($userId);

        return response()->json([
            'success' => true
        ]);
    }
}
