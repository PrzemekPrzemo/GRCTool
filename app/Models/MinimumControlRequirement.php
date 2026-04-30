<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MinimumControlRequirement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'minimum_control_requirements';

    protected $fillable = [
        'code', 'name', 'description', 'vendor_facing_text',
        'category', 'subcategory', 'severity',
        'framework_mappings', 'linked_internal_control_id',
        'expected_evidence_types', 'vendor_tier_applicability',
        'evaluation_criteria', 'requires_evidence',
        'is_active', 'order',
    ];

    protected $casts = [
        'framework_mappings' => 'array',
        'expected_evidence_types' => 'array',
        'vendor_tier_applicability' => 'array',
        'requires_evidence' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function linkedControl(): BelongsTo
    {
        return $this->belongsTo(Control::class, 'linked_internal_control_id');
    }

    public function appliesToTier(string $tier): bool
    {
        $applicable = $this->vendor_tier_applicability ?? [];
        if (empty($applicable)) {
            return true;
        }

        return in_array($tier, $applicable, true);
    }
}
