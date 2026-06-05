<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceRequirement extends Model
{
    protected $fillable = [
        'domain_id', 'code', 'name', 'description', 'guidance',
        'control_type', 'is_mandatory', 'sort_order',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'sort_order'   => 'integer',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(ComplianceDomain::class, 'domain_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ComplianceResponse::class, 'requirement_id');
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(ComplianceRequirementMapping::class, 'requirement_id');
    }

    public function mappedTo(): HasMany
    {
        return $this->hasMany(ComplianceRequirementMapping::class, 'mapped_requirement_id');
    }
}
