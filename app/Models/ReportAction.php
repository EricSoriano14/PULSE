<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportAction extends Model
{
    protected $fillable = [
        'report_id',

        // Faculty recommendation
        'recommended_by_user_id',
        'recommended_action',
        'recommended_note',
        'recommended_at',

        // CSS / Admin decision
        'decided_by_user_id',
        'decision',
        'decision_public_remark',
        'decision_internal_note',
        'decision_at',

        // Action taken
        'action_taken_by_user_id',
        'action_taken_note',
        'action_taken_at',
    ];

    protected $casts = [
        'recommended_at' => 'datetime',
        'decision_at' => 'datetime',
        'action_taken_at' => 'datetime',
    ];

    /**
     * Phase 3 & 4: Report this action belongs to
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Phase 3 & 4: Faculty who made recommendation
     */
    public function recommendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recommended_by_user_id');
    }

    /**
     * Phase 3 & 4: CSS/Admin who made the decision
     */
    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }

    /**
     * Phase 3 & 4: Staff who performed the action taken
     */
    public function actionTakenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_taken_by_user_id');
    }
}
