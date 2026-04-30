<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'engagement_id', 'code', 'control_id', 'requested_by',
        'requested_at', 'due_date', 'description', 'sample_criteria', 'status',
        'provided_by', 'provided_at', 'reviewer_notes',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'due_date' => 'date',
        'provided_at' => 'datetime',
    ];

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(AuditEngagement::class, 'engagement_id');
    }

    public function control(): BelongsTo
    {
        return $this->belongsTo(Control::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provided_by');
    }
}
