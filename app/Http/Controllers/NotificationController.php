<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->paginate(15);

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $this->getUnreadCount($user->id),
        ]);
    }

    public function markAsRead(Notification $notification)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        if ((int) $notification->user_id !== (int) $user->id) {
            abort(404);
        }

        if ($notification->read_at === null) {
            $notification->read_at = now();
            $notification->save();
        }

        // IMPORTANT:
        // Mark as read should ONLY mark it as read.
        // It must NOT redirect to the report automatically.
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'All notifications marked as read.');
    }

    private function getUnreadCount(int $userId): int
    {
        return Notification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }
}