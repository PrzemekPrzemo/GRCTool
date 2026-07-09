<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceObligation extends Model
{
    protected $fillable = [
        'ref', 'category', 'regulation', 'obligation', 'applies_when',
        'related_documents', 'owner_id', 'status', 'last_reviewed_at',
        'next_review_at', 'notes', 'sort_order',
    ];

    protected $casts = [
        'last_reviewed_at' => 'date',
        'next_review_at' => 'date',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
