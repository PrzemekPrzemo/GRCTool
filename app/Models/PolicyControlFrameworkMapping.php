<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyControlFrameworkMapping extends Model
{
    protected $fillable = [
        'policy_control_id', 'framework_code', 'control_ref', 'mapping_type',
    ];

    public function policyControl(): BelongsTo
    {
        return $this->belongsTo(PolicyControl::class);
    }

    public function framework(): BelongsTo
    {
        return $this->belongsTo(ComplianceFramework::class, 'framework_code', 'code');
    }
}
