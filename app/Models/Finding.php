<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Finding extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'title', 'description', 'source', 'engagement_id',
        'severity', 'framework_reference',
        'linked_control_id', 'linked_risk_id',
        'discovered_at', 'due_date', 'closed_at',
        'status', 'owner_id', 'evidence_of_closure',
        'verified_by', 'verified_at',
    ];

    protected $casts = [
        'discovered_at' => 'date',
        'due_date' => 'date',
        'closed_at' => 'date',
        'verified_at' => 'datetime',
    ];

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(AuditEngagement::class, 'engagement_id');
    }

    public function control(): BelongsTo
    {
        return $this->belongsTo(Control::class, 'linked_control_id');
    }

    public function risk(): BelongsTo
    {
        return $this->belongsTo(Risk::class, 'linked_risk_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }
}
