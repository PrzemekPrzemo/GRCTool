<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dpia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dpias';

    protected $fillable = [
        'code', 'title', 'description',
        'processing_activity_id', 'conducted_by', 'assessment_date',
        'necessity_assessment', 'proportionality_assessment',
        'identified_risks', 'overall_risk_level', 'mitigation_measures',
        'dpo_consulted', 'dpo_opinion', 'dpo_consulted_at',
        'authority_consultation_required', 'authority_consulted_at', 'authority_response',
        'status', 'reviewed_by', 'reviewed_at', 'review_notes',
    ];

    protected $casts = [
        'assessment_date'       => 'date',
        'dpo_consulted_at'      => 'date',
        'authority_consulted_at' => 'date',
        'reviewed_at'           => 'datetime',
        'identified_risks'      => 'array',
        'mitigation_measures'   => 'array',
        'dpo_consulted'         => 'boolean',
        'authority_consultation_required' => 'boolean',
    ];

    const RISK_LEVELS = [
        'low'       => 'Niskie',
        'medium'    => 'Średnie',
        'high'      => 'Wysokie',
        'very_high' => 'Bardzo wysokie',
    ];

    public function processingActivity(): BelongsTo
    {
        return $this->belongsTo(ProcessingActivity::class);
    }

    public function conductedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function riskLevelLabel(): string
    {
        return self::RISK_LEVELS[$this->overall_risk_level] ?? ($this->overall_risk_level ?? '—');
    }
}
