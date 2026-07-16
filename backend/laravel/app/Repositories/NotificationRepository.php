<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Models\User;

class NotificationRepository
{
    public function createNotification(Array $inputs, $user_id)
    {
        return Notification::create([
            'user_id' => $user_id,
            'title' => $inputs['title'],
            'message' => $inputs['message'],
            'type' => $inputs['type'],
            'is_read' => $inputs['is_read'] ?? false,
        ]);
    }

    public function getNotifications()
    {
        $user = auth()->user();

        return [
            'all' => $user->notifications()->latest()->get(),
            'read' => $user->readNotifications()->latest()->get(),
            'unread' => $user->unreadNotifications()->latest()->get(),
        ];
    }

    public function setNotificationAsRead($notification_id)
    {
        return Notification::where('id', $notification_id)->update(['is_read' => true]);
    }
}   