<?php

namespace App\Http\Controllers;

use App\Repositories\NotificationRepository;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationRepository;
    
    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function createNotification(Array $inputs, $user_id)
    {
        return $this->notificationRepository->createNotification($inputs, $user_id);
    }

    public function getNotifications($user_id)
    {
        return $this->notificationRepository->getNotifications($user_id);
    }

    public function setNotificationAsRead($notification_id, $user_id)
    {
        return $this->notificationRepository->setNotificationAsRead($notification_id, $user_id);
    }
}