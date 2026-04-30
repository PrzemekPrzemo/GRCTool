<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskAcceptance extends Model
{
    use HasFactory;

    protected $fillable = [
        'risk_id', 'proposed_by', 'proposed_at',
        'accepted_by', 'accepted_at', 'expiry_date',
        'rationale', 'compensating_controls', 'evidence_id',
        'revoked_at', 'revoked_by', 'revoke_reason', 'status',
    ];

    protected $casts = [
        'proposed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'revoked_at' => 'datetime',
        'expiry_date' => 'date',
        'compensating_controls' => 'array',
    ];

    public function risk(): BelongsTo
    {
        return $this->belongsTo(Risk::class);
    }

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function evidence(): BelongsTo
    {
        return $this->belongsTo(EvidenceObject::class, 'evidence_id');
    }
}
