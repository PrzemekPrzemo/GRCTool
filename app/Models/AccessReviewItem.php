<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessReviewItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id', 'subject_user_id', 'subject_name',
        'reviewer_id', 'system_name', 'access_role', 'access_scope',
        'last_used_at', 'justification',
        'status', 'decision_note', 'reviewed_at', 'reviewed_by',
    ];

    protected $casts = [
        'subject_user_id' => 'integer',
        'reviewer_id' => 'integer',
        'reviewed_by' => 'integer',
        'last_used_at' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public const STATUS_LABELS = [
        'pending' => 'Oczekuje',
        'approved' => 'Zatwierdzone',
        'revoked' => 'Odwołane',
        'modified' => 'Zmodyfikowane',
        'not_reviewed' => 'Nieprzejrzane',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AccessReviewCampaign::class);
    }

    public function subjectUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
