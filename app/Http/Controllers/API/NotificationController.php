<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        if ($request->query('unread') === 'true') {
            $notifications = $user->unreadNotifications;
        } else {
            $notifications = $user->notifications;
        }

        return response()->json(['notifications' => $notifications]);
    }
    public function markAsRead($id): JsonResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function markAllAsRead(): JsonResponse
    {
        foreach (auth()->user()->unreadNotifications as $notification) {
            $notification->markAsRead();
        }

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
