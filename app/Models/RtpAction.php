<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RtpAction extends Model
{
    use HasFactory;

    protected $table = 'rtp_actions';

    protected $fillable = [
        'rtp_id', 'title', 'description', 'owner_id', 'due_date',
        'cost_eur', 'status', 'progress_percent', 'linked_controls', 'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'date',
        'cost_eur' => 'decimal:2',
        'linked_controls' => 'array',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(RiskTreatmentPlan::class, 'rtp_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && ! in_array($this->status, ['Completed', 'Cancelled'], true);
    }

    public function daysOverdue(): int
    {
        if (! $this->due_date) {
            return 0;
        }

        return (int) now()->diffInDays($this->due_date, false) * -1;
    }
}
