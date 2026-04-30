<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorAssessmentResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id', 'mcr_id',
        'response_value', 'vendor_evidence_text', 'vendor_evidence_files', 'vendor_responded_at',
        'our_review_status', 'our_review_notes', 'reviewed_by', 'reviewed_at',
        'gap_severity', 'remediation_plan', 'remediation_due_date',
        'exception_granted', 'exception_rationale',
    ];

    protected $casts = [
        'vendor_evidence_files' => 'array',
        'vendor_responded_at' => 'date',
        'reviewed_at' => 'datetime',
        'remediation_due_date' => 'date',
        'exception_granted' => 'boolean',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(VendorAssessment::class, 'assessment_id');
    }

    public function mcr(): BelongsTo
    {
        return $this->belongsTo(MinimumControlRequirement::class, 'mcr_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
