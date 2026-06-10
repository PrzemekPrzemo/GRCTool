<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Risk extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'title', 'description', 'category_l1', 'category_l2', 'scenario_template_id',
        'risk_scenario', 'threat_actors', 'mitre_attack_techniques',
        'inherent_likelihood', 'inherent_impact', 'inherent_score',
        'inherent_lef_distribution', 'inherent_lm_distribution',
        'residual_likelihood', 'residual_impact', 'residual_score',
        'residual_ale_eur', 'residual_var95_eur',
        'target_score', 'target_date', 'risk_appetite_breach',
        'treatment_strategy', 'owner_id', 'business_unit_id',
        'linked_clients', 'linked_projects', 'linked_assets', 'linked_controls',
        'linked_indicators', 'linked_findings', 'linked_incidents', 'mapped_frameworks',
        'review_frequency', 'next_review_date', 'last_reviewed_at', 'last_reviewed_by',
        'status',
    ];

    protected $casts = [
        'threat_actors' => 'array',
        'mitre_attack_techniques' => 'array',
        'inherent_lef_distribution' => 'array',
        'inherent_lm_distribution' => 'array',
        'linked_clients' => 'array',
        'linked_projects' => 'array',
        'linked_assets' => 'array',
        'linked_controls' => 'array',
        'linked_indicators' => 'array',
        'linked_findings' => 'array',
        'linked_incidents' => 'array',
        'mapped_frameworks' => 'array',
        'risk_appetite_breach' => 'boolean',
        'target_date' => 'date',
        'next_review_date' => 'date',
        'last_reviewed_at' => 'date',
        'residual_ale_eur' => 'decimal:2',
        'residual_var95_eur' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (Risk $risk): void {
            $risk->inherent_score = (int) $risk->inherent_likelihood * (int) $risk->inherent_impact;
            $risk->residual_score = (int) $risk->residual_likelihood * (int) $risk->residual_impact;
        });
    }

    public function scenarioTemplate(): BelongsTo
    {
        return $this->belongsTo(ScenarioTemplate::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function lastReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_reviewed_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(RiskVersion::class);
    }

    public function acceptances(): HasMany
    {
        return $this->hasMany(RiskAcceptance::class);
    }

    public function activeAcceptance(): HasOne
    {
        return $this->hasOne(RiskAcceptance::class)->where('status', 'Approved')->whereNull('revoked_at');
    }

    public function treatmentPlans(): HasMany
    {
        return $this->hasMany(RiskTreatmentPlan::class);
    }

    public function evidence(): MorphMany
    {
        return $this->morphMany(EvidenceLink::class, 'linkable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }

    public function riskLevel(): string
    {
        return match (true) {
            $this->residual_score >= 20 => 'Critical',
            $this->residual_score >= 12 => 'High',
            $this->residual_score >= 6 => 'Medium',
            default => 'Low',
        };
    }
}
