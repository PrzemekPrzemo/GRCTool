<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'policy_id', 'version_number', 'snapshot', 'diff',
        'document_evidence_id', 'changed_by', 'change_reason', 'changed_at',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'diff' => 'array',
        'changed_at' => 'datetime',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(EvidenceObject::class, 'document_evidence_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
