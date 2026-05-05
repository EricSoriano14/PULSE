<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class StudentNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user || strtolower((string) $user->role) !== 'student') {
            abort(403, 'Unauthorized.');
        }

        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min($perPage, 50));

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->paginate($perPage);

        return response()->json($notifications->through(function (Notification $notification) {
            return $this->transformNotification($notification);
        }));
    }

    public function unreadCount(Request $request)
    {
        $user = $request->user();

        if (!$user || strtolower((string) $user->role) !== 'student') {
            abort(403, 'Unauthorized.');
        }

        $count = Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'unread_count' => $count,
        ]);
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        $user = $request->user();

        if (!$user || strtolower((string) $user->role) !== 'student') {
            abort(403, 'Unauthorized.');
        }

        if ((int) $notification->user_id !== (int) $user->id) {
            abort(404);
        }

        if ($notification->read_at === null) {
            $notification->read_at = now();
            $notification->save();
        }

        return response()->json([
            'message' => 'Notification marked as read.',
            'notification' => $this->transformNotification($notification->fresh()),
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        if (!$user || strtolower((string) $user->role) !== 'student') {
            abort(403, 'Unauthorized.');
        }

        Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'All notifications marked as read.',
        ]);
    }

    private function transformNotification(Notification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'message' => $notification->message,
            'report_id' => $notification->report_id,
            'announcement_id' => $notification->announcement_id,
            'target_url' => $notification->target_url,
            'is_read' => $notification->read_at !== null,
            'read_at' => optional($notification->read_at)?->toISOString(),
            'created_at' => optional($notification->created_at)?->toISOString(),
            'time_ago' => optional($notification->created_at)?->diffForHumans(),
        ];
    }
}