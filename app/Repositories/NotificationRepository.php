<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Repositories\Contracts\NotificationRepositoryInterface;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function getUnreadByUserId(int $userId)
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getAllByUserId(int $userId, int $limit = 20)
    {
        return Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function markAsRead(int $id): bool
    {
        $notification = Notification::find($id);
        if ($notification) {
            return $notification->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }
        return false;
    }

    public function markAllAsRead(int $userId): bool
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]) > 0;
    }

    public function create(array $data): Notification
    {
        return Notification::create($data);
    }
}
