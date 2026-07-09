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
        'code', 'title', 'title_en', 'description', 'scope_description', 'category',
        'isms_type', 'classification', 'current_version', 'effective_from', 'next_review_due',
        'review_cycle_months', 'owner_id', 'owner_role', 'status', 'approved_by', 'approved_at',
        'framework_mappings', 'attestation_required', 'parent_policy_id', 'document_ref', 'audience',
        'supersedes_policy_id',
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

    public function parentPolicy(): BelongsTo
    {
        return $this->belongsTo(Policy::class, 'parent_policy_id');
    }

    public function childPolicies(): HasMany
    {
        return $this->hasMany(Policy::class, 'parent_policy_id');
    }

    public function controls(): HasMany
    {
        return $this->hasMany(PolicyControl::class);
    }

    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(Policy::class, 'supersedes_policy_id');
    }

    public function supersededBy(): HasMany
    {
        return $this->hasMany(Policy::class, 'supersedes_policy_id');
    }
}
