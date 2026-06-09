<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RiskTreatmentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'risk_id', 'target_residual_score', 'target_date', 'budget_eur',
        'acceptance_required_by', 'review_cadence', 'status',
        'approved_by', 'approved_at',
    ];

    protected $casts = [
        'target_date' => 'date',
        'approved_at' => 'datetime',
        'budget_eur' => 'decimal:2',
        'acceptance_required_by' => 'array',
    ];

    public function risk(): BelongsTo
    {
        return $this->belongsTo(Risk::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(RtpAction::class, 'rtp_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function progressPercent(): int
    {
        $count = $this->actions()->count();
        if ($count === 0) {
            return 0;
        }

        return (int) round($this->actions()->sum('progress_percent') / $count);
    }

    public function totalCostEur(): float
    {
        return (float) $this->actions()->sum('cost_eur');
    }

    public function budgetVariance(): float
    {
        return (float) ($this->budget_eur ?? 0) - $this->totalCostEur();
    }

    public function overdueActionsCount(): int
    {
        return $this->actions()
            ->whereNotIn('status', ['Completed', 'Cancelled'])
            ->whereDate('due_date', '<', now())
            ->count();
    }
}
