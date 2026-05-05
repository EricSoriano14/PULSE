<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        // Phase 3
        'user_id',
        'calamity',
        'department',
        'status',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',

        // NEW (Update 1)
        'assigned_faculty_id',

        // NEW (Co-CSS Assignment)
        'assigned_co_css_id',

        // Phase 4
        'student_id',
        'title',
        'calamity_type',
        'category',
        'description',
        'affected_subject_or_class',
        'latitude',
        'longitude',
        'location_address',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /* =========================
       Phase 3 (Web)
    ========================= */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Instructor selected by the student
     */
    public function assignedFaculty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_faculty_id');
    }

    /**
     * Co-CSS assigned by CSS/Admin
     */
    public function assignedCoCss(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_co_css_id');
    }

    public function action(): HasOne
    {
        return $this->hasOne(ReportAction::class);
    }

    /* =========================
       Phase 4 (Student API)
    ========================= */

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ReportImage::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ReportAction::class, 'report_id');
    }

    /**
     * Latest action visible to student
     */
    public function latestStudentVisibleAction(): HasOne
    {
        return $this->hasOne(ReportAction::class, 'report_id')
            ->latest('id')
            ->select([
                'id',
                'report_id',
                'recommended_action',
                'recommended_note',
                'recommended_at',
                'public_remark',
                'action_taken_note',
                'created_at',
            ]);
    }
}