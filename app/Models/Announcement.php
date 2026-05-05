<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        // Phase 3 fields (keep)
        'user_id',
        'department',
        'description',
        'image_path',

        // Phase 4 fields (final schema; safe to include)
        'posted_by_user_id',
        'image_url',
        'department_target_id',
        'course_target_id',
        'status',
        'canceled_at',
    ];

    protected $casts = [
        'canceled_at' => 'datetime',
    ];

    /**
     * Phase 3 (Web): legacy relation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Phase 4 (Final schema): who posted the announcement
     */
    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by_user_id');
    }

    /**
     * Phase 4: department targeting
     */
    public function departmentTarget(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_target_id');
    }

    /**
     * Phase 4: course targeting
     */
    public function courseTarget(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_target_id');
    }
}
