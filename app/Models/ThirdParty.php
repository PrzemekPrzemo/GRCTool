<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThirdParty extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'third_parties';

    protected $fillable = [
        'code', 'name', 'service_provided', 'data_categories',
        'country_of_processing', 'legal_basis', 'transfer_mechanism',
        'dpa_url', 'certifications', 'tier',
        'last_assessment_date', 'next_assessment_due',
        'security_rating', 'rating_history', 'is_active',
    ];

    protected $casts = [
        'data_categories' => 'array',
        'certifications' => 'array',
        'rating_history' => 'array',
        'last_assessment_date' => 'date',
        'next_assessment_due' => 'date',
        'is_active' => 'boolean',
    ];

    public function processingActivities(): BelongsToMany
    {
        return $this->belongsToMany(ProcessingActivity::class, 'processing_activity_third_party')
            ->withPivot('role', 'notes')
            ->withTimestamps();
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(VendorAssessment::class);
    }

    /**
     * Composite risk score (0-100, higher = riskier) from tier, external security rating,
     * latest vendor assessment compliance %, critical gap count, and assessment staleness.
     *
     * @return array{score: int, tier: string, factors: array<string, int>, latest_assessment: ?VendorAssessment}
     */
    public function computeRiskScore(): array
    {
        $latest = $this->assessments()->whereNotNull('completed_at')->orderByDesc('completed_at')->first();

        $factors = [
            'tier' => match ($this->tier) {
                'Critical' => 30,
                'High' => 20,
                'Medium' => 10,
                default => 0,
            },
            'security_rating' => $this->security_rating !== null
                ? (int) round((100 - $this->security_rating) / 100 * 30)
                : 15,
            'compliance' => $latest?->compliance_percentage !== null
                ? (int) round((100 - (float) $latest->compliance_percentage) / 100 * 25)
                : 12,
            'critical_gaps' => min((int) ($latest?->critical_gaps_count ?? 0) * 5, 15),
            'overdue' => ($this->next_assessment_due && $this->next_assessment_due->isPast()) ? 10 : 0,
        ];

        $score = min(100, array_sum($factors));
        $tier = match (true) {
            $score >= 70 => 'Critical',
            $score >= 45 => 'High',
            $score >= 20 => 'Medium',
            default => 'Low',
        };

        return ['score' => $score, 'tier' => $tier, 'factors' => $factors, 'latest_assessment' => $latest];
    }

    public function recordRiskSnapshot(): void
    {
        $history = $this->rating_history ?? [];
        $history[] = ['date' => now()->toDateString(), 'score' => $this->computeRiskScore()['score']];
        $this->update(['rating_history' => array_slice($history, -24)]);
    }
}
