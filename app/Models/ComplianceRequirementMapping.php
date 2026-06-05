<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceRequirementMapping extends Model
{
    protected $fillable = [
        'requirement_id', 'mapped_requirement_id', 'mapping_type', 'notes',
    ];

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(ComplianceRequirement::class, 'requirement_id');
    }

    public function mappedRequirement(): BelongsTo
    {
        return $this->belongsTo(ComplianceRequirement::class, 'mapped_requirement_id');
    }
}
