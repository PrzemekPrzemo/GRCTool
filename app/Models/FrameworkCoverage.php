<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrameworkCoverage extends Model
{
    protected $table = 'framework_coverage';

    protected $fillable = [
        'framework_code', 'jurisdiction', 'total_controls_in_standard', 'controls_mapped',
        'coverage_estimate_pct', 'status', 'gaps_note', 'next_steps', 'extra',
    ];

    protected $casts = [
        'jurisdiction' => 'array',
        'next_steps' => 'array',
        'extra' => 'array',
        'coverage_estimate_pct' => 'decimal:2',
    ];

    public function framework(): BelongsTo
    {
        return $this->belongsTo(ComplianceFramework::class, 'framework_code', 'code');
    }
}
