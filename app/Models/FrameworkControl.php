<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FrameworkControl extends Model
{
    use HasFactory;

    protected $fillable = [
        'framework_version_id', 'reference', 'parent_reference',
        'title', 'description', 'domain', 'subdomain', 'attributes', 'order',
    ];

    protected $casts = [
        'attributes' => 'array',
    ];

    public function frameworkVersion(): BelongsTo
    {
        return $this->belongsTo(FrameworkVersion::class);
    }

    public function controls(): BelongsToMany
    {
        return $this->belongsToMany(Control::class, 'control_framework_mappings')
            ->withPivot('mapping_type', 'mapping_rationale')
            ->withTimestamps();
    }
}
