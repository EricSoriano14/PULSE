<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Notification;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    public function createForUser(
        User $user,
        string $type,
        string $title,
        string $message,
        ?Report $report = null,
        ?Announcement $announcement = null,
        ?User $createdBy = null,
        ?string $targetUrl = null
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'report_id' => $report?->id,
            'announcement_id' => $announcement?->id,
            'created_by_user_id' => $createdBy?->id,
            'target_url' => $targetUrl,
        ]);
    }

    public function createForUsers(
        iterable $users,
        string $type,
        string $title,
        string $message,
        ?Report $report = null,
        ?Announcement $announcement = null,
        ?User $createdBy = null,
        ?string $targetUrl = null
    ): void {
        $now = now();

        $rows = collect($users)
            ->filter(fn ($user) => $user instanceof User)
            ->unique('id')
            ->map(function (User $user) use (
                $type,
                $title,
                $message,
                $report,
                $announcement,
                $createdBy,
                $targetUrl,
                $now
            ) {
                return [
                    'user_id' => $user->id,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'report_id' => $report?->id,
                    'announcement_id' => $announcement?->id,
                    'created_by_user_id' => $createdBy?->id,
                    'target_url' => $targetUrl,
                    'read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->values()
            ->all();

        if (!empty($rows)) {
            Notification::insert($rows);
        }
    }
}