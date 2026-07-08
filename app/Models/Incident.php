<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incident extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'title', 'description', 'severity', 'status', 'source', 'source_ref',
        'occurred_at', 'detected_at', 'acknowledged_at', 'contained_at', 'resolved_at',
        'owner_id', 'affected_user_id', 'is_breach',
        'affected_clients', 'affected_assets', 'linked_risks', 'linked_controls',
        'post_mortem', 'estimated_cost_eur',
        // ENISA NIS2 Art. 23 scoring
        'enisa_users_affected_band', 'enisa_service_impact', 'enisa_geographic_spread',
        'enisa_duration_hours', 'enisa_economic_impact',
        'enisa_severity_score', 'enisa_severity_level', 'enisa_is_significant',
        'enisa_early_warning_deadline', 'enisa_notification_deadline', 'enisa_final_report_deadline',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'detected_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'contained_at' => 'datetime',
        'resolved_at' => 'datetime',
        'is_breach' => 'boolean',
        'affected_clients' => 'array',
        'affected_assets' => 'array',
        'linked_risks' => 'array',
        'linked_controls' => 'array',
        'estimated_cost_eur' => 'decimal:2',
        // ENISA
        'enisa_duration_hours' => 'decimal:2',
        'enisa_severity_score' => 'decimal:2',
        'enisa_is_significant' => 'boolean',
        'enisa_early_warning_deadline' => 'datetime',
        'enisa_notification_deadline' => 'datetime',
        'enisa_final_report_deadline' => 'datetime',
    ];

    // ENISA severity classification weights (ENISA Technical Guidelines for NIS2)
    private const ENISA_USER_SCORES = ['lt100' => 0, 'lt1k' => 1, 'lt10k' => 2, 'lt100k' => 2, 'ge100k' => 3];

    private const ENISA_SERVICE_SCORES = ['none' => 0, 'minimal' => 1, 'partial' => 1, 'significant' => 2, 'full' => 3];

    private const ENISA_GEO_SCORES = ['local' => 0, 'regional' => 1, 'national' => 2, 'cross_border' => 3];

    private const ENISA_ECONOMIC_SCORES = ['negligible' => 0, 'low' => 1, 'moderate' => 2, 'significant' => 2, 'severe' => 3];

    public const SEVERITIES = ['Critical', 'High', 'Medium', 'Low'];

    public const STATUSES = ['New', 'Investigating', 'Containment', 'Eradication', 'Recovery', 'Closed'];

    public const SOURCES = [
        'SIEM', 'Manual', 'Customer', 'IR Process', 'Threat Intel', 'Other',
        'Entra ID Identity Protection', 'Google Workspace',
    ];

    protected static function booted(): void
    {
        static::saving(function (Incident $incident): void {
            $incident->calculateEnisaScore();
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function affectedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'affected_user_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }

    public function mttdMinutes(): ?int
    {
        if (! $this->occurred_at || ! $this->detected_at) {
            return null;
        }

        return (int) $this->occurred_at->diffInMinutes($this->detected_at);
    }

    public function mttaMinutes(): ?int
    {
        if (! $this->detected_at || ! $this->acknowledged_at) {
            return null;
        }

        return (int) $this->detected_at->diffInMinutes($this->acknowledged_at);
    }

    public function mttrHours(): ?int
    {
        if (! $this->detected_at || ! $this->resolved_at) {
            return null;
        }

        return (int) $this->detected_at->diffInHours($this->resolved_at);
    }

    /**
     * Compute ENISA NIS2 Art.23 impact score from five dimensions.
     * Weights: users 25%, service 25%, geographic 20%, duration 20%, economic 10%.
     * Score range 0.00–3.00. Significant incident threshold: >= 1.5.
     */
    public function calculateEnisaScore(): void
    {
        $uScore = self::ENISA_USER_SCORES[$this->enisa_users_affected_band] ?? null;
        $sScore = self::ENISA_SERVICE_SCORES[$this->enisa_service_impact] ?? null;
        $gScore = self::ENISA_GEO_SCORES[$this->enisa_geographic_spread] ?? null;
        $eScore = self::ENISA_ECONOMIC_SCORES[$this->enisa_economic_impact] ?? null;

        $hours = $this->enisa_duration_hours !== null ? (float) $this->enisa_duration_hours : null;
        $dScore = $hours === null ? null : match (true) {
            $hours >= 24 => 3,
            $hours >= 4 => 2,
            $hours >= 1 => 1,
            default => 0,
        };

        if ($uScore === null || $sScore === null || $gScore === null || $dScore === null || $eScore === null) {
            $this->enisa_severity_score = null;
            $this->enisa_severity_level = null;
            $this->enisa_is_significant = null;

            return;
        }

        $score = round($uScore * 0.25 + $sScore * 0.25 + $gScore * 0.20 + $dScore * 0.20 + $eScore * 0.10, 2);

        $this->enisa_severity_score = $score;
        $this->enisa_severity_level = match (true) {
            $score >= 2.25 => 'Critical',
            $score >= 1.5 => 'High',
            $score >= 0.75 => 'Medium',
            default => 'Low',
        };
        $this->enisa_is_significant = $score >= 1.5;

        // NIS2 notification deadlines only when both breach AND significant
        if ($this->is_breach && $this->enisa_is_significant && $this->detected_at) {
            $dt = Carbon::parse($this->detected_at);
            $this->enisa_early_warning_deadline = $dt->copy()->addHours(24);
            $this->enisa_notification_deadline = $dt->copy()->addHours(72);
            $this->enisa_final_report_deadline = $dt->copy()->addDays(30);
        } else {
            $this->enisa_early_warning_deadline = null;
            $this->enisa_notification_deadline = null;
            $this->enisa_final_report_deadline = null;
        }
    }
}
