<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Http\Requests\Notifications\MarkReadNotificationsRequest;
use App\Services\IndexService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications
     */
    public function index(Request $request)
    {
        $perPage = IndexService::limitPerPage($request->query('perPage', 10));
        $page = IndexService::checkPageIfNull($request->query('page', 1));
        $search = IndexService::checkIfSearchEmpty($request->query('search'));

        $user = auth()->user();
        $notifications = Notification::where('user_id', $user->id)->latest();

        if ($search) {
            $notifications->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('title', 'like', '%' . $search . '%')
                      ->orWhere('message', 'like', '%' . $search . '%')
                      ->orWhere('type', 'like', '%' . $search . '%');
            });
        }

        $notifications = $notifications->paginate($perPage, ['*'], 'notification', $page);

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'notifications' => $notifications->items(),
                'pagination' => IndexService::handlePagination($notifications)
            ]);
        }

        if($user->company){
            return inertia('Company/Notifications/Index');
        }

        return inertia('Admin/Notifications/Index');
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount()
    {
        $count = NotificationService::getUnreadCount();

        return response()->json([
            'status' => 'success',
            'count' => $count
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(MarkReadNotificationsRequest $request, Notification $notification)
    {
        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        NotificationService::markAllAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read'
        ]);
    }
} 