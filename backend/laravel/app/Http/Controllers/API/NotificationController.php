<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationCreateRequest;
use App\Repositories\NotificationRepository;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationRepository;
    
    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function createNotification(NotificationCreateRequest $request, $user_id)
    {
        return $this->notificationRepository->createNotification($request->all(), $user_id);
    }

    public function getNotifications()
    {
        return response()->json([
            'success' => true,
            'data' => $this->notificationRepository->getNotifications(),
        ]);
    }

    public function setNotificationAsRead(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->notificationRepository->setNotificationAsRead($request->id),
        ]);
    }
}