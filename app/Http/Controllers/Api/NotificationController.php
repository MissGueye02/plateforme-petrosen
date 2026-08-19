<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /api/notifications
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Notification::where('utilisateur_id', $user->id)->orderBy('created_at', 'desc');

        $notifications = $query->paginate(20);
        $unreadCount = Notification::where('utilisateur_id', $user->id)->where('lu', false)->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'data' => $notifications,
        ]);
    }

    // GET /api/notifications/unread-count
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = Notification::where('utilisateur_id', $user->id)->where('lu', false)->count();
        return response()->json(['unread_count' => $count]);
    }

    // PATCH /api/notifications/{notification}/lu
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        $user = $request->user();
        if ($notification->utilisateur_id !== $user->id) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $notification->lu = true;
        $notification->save();

        return response()->json(['message' => 'Notification marquée comme lue.', 'notification' => $notification]);
    }

    // PATCH /api/notifications/mark-all-lu
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        Notification::where('utilisateur_id', $user->id)->where('lu', false)->update(['lu' => true]);
        return response()->json(['message' => 'Toutes les notifications marquées comme lues.']);
    }
}
