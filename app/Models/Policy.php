<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Policy extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'policies';

    protected $fillable = [
        'code', 'title', 'description', 'category', 'current_version',
        'effective_from', 'next_review_due', 'owner_id', 'status',
        'approved_by', 'approved_at', 'framework_mappings', 'attestation_required',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'next_review_due' => 'date',
        'approved_at' => 'datetime',
        'framework_mappings' => 'array',
        'attestation_required' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function attestations(): HasMany
    {
        return $this->hasMany(PolicyAttestation::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PolicyVersion::class)->orderByDesc('changed_at');
    }

    public function documentLinks(): MorphMany
    {
        return $this->morphMany(EvidenceLink::class, 'linkable');
    }
}
