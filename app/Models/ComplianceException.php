<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplianceException extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'title', 'exception_type', 'subject_type', 'subject_id',
        'rationale', 'compensating_controls', 'affected_frameworks',
        'requested_by', 'approved_by', 'approved_at', 'expires_at',
        'status', 'rejection_reason',
    ];

    protected $casts = [
        'affected_frameworks' => 'array',
        'approved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public const TYPE_LABELS = [
        'control' => 'Kontrola',
        'risk' => 'Ryzyko',
        'policy' => 'Polityka',
        'vulnerability' => 'Podatność',
        'other' => 'Inne',
    ];

    public const STATUS_LABELS = [
        'draft' => 'Szkic',
        'pending_approval' => 'Oczekuje',
        'approved' => 'Zatwierdzony',
        'rejected' => 'Odrzucony',
        'expired' => 'Wygasł',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isPast()
            && $this->status === 'approved';
    }
}
