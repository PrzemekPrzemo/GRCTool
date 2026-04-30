<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EvidenceLink extends Model
{
    protected $fillable = [
        'evidence_id', 'linkable_type', 'linkable_id', 'relation_role',
    ];

    public function evidence(): BelongsTo
    {
        return $this->belongsTo(EvidenceObject::class, 'evidence_id');
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }
}
