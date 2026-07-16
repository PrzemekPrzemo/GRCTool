<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceResponse extends Model
{
    protected $fillable = [
        'assessment_id', 'requirement_id', 'status',
        'evidence', 'gap_description', 'remediation_plan',
        'priority', 'target_date', 'responded_by', 'responded_at',
    ];

    protected $casts = [
        'target_date' => 'date',
        'responded_at' => 'datetime',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(ComplianceAssessment::class, 'assessment_id');
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(ComplianceRequirement::class, 'requirement_id');
    }

    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
}
