<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceDomain extends Model
{
    protected $fillable = [
        'framework_id', 'code', 'name', 'description', 'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function framework(): BelongsTo
    {
        return $this->belongsTo(ComplianceFramework::class, 'framework_id');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(ComplianceRequirement::class, 'domain_id')->orderBy('sort_order');
    }
}
