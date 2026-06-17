<?php

namespace App\Repositories\Contracts;

use App\Models\Notification;

interface NotificationRepositoryInterface
{
    public function getUnreadByUserId(int $userId);
    public function getAllByUserId(int $userId, int $limit = 20);
    public function markAsRead(int $id): bool;
    public function markAllAsRead(int $userId): bool;
    public function create(array $data): Notification;
}
