<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BcpPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'title', 'description', 'plan_type', 'scope',
        'owner_id', 'rto_hours', 'rpo_minutes', 'mtd_hours',
        'linked_assets', 'linked_risks', 'status', 'version',
        'last_reviewed_at', 'next_review_due', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'linked_assets' => 'array',
        'linked_risks' => 'array',
        'last_reviewed_at' => 'date',
        'next_review_due' => 'date',
        'approved_at' => 'datetime',
    ];

    public const TYPE_LABELS = [
        'bcp' => 'BCP',
        'dr' => 'DR',
        'coop' => 'COOP',
        'crisis' => 'Kryzys',
    ];

    public const STATUS_LABELS = [
        'draft' => 'Szkic',
        'active' => 'Aktywny',
        'under_review' => 'W przeglądzie',
        'retired' => 'Wycofany',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function tests(): HasMany
    {
        return $this->hasMany(BcpTest::class, 'bcp_plan_id');
    }

    public function latestTest(): ?BcpTest
    {
        return $this->tests()->orderByDesc('tested_at')->first();
    }

    public function isReviewOverdue(): bool
    {
        return $this->next_review_due !== null && $this->next_review_due->isPast();
    }

    public function rtoLabel(): string
    {
        if ($this->rto_hours === null) {
            return '—';
        }
        $hours = (float) $this->rto_hours;
        if ($hours >= 24) {
            $days = round($hours / 24, 1);

            return "{$days}d";
        }

        return "{$hours}h";
    }
}
