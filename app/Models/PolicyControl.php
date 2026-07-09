<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PolicyControl extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'control_code', 'policy_id', 'title', 'description', 'section_ref',
        'control_type', 'implementation_type', 'status',
        'owner_role', 'evidence_type', 'review_frequency', 'data_classification_scope',
    ];

    protected $casts = [
        'data_classification_scope' => 'array',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    public function frameworkMappings(): HasMany
    {
        return $this->hasMany(PolicyControlFrameworkMapping::class);
    }
}
