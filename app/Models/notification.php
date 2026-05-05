<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'report_id',
        'announcement_id',
        'created_by_user_id',
        'target_url',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public const TYPE_STUDENT_ANNOUNCEMENT = 'student_announcement';
    public const TYPE_STUDENT_REPORT_UPDATE = 'student_report_update';
    public const TYPE_STAFF_NEW_REPORT = 'staff_new_report';
    public const TYPE_STAFF_CO_CSS_ASSIGNMENT = 'staff_co_css_assignment';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->forceFill([
                'read_at' => now(),
            ])->save();
        }
    }
}