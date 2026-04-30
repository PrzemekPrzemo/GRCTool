<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Control extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'description', 'control_type', 'automation_level',
        'owner_id', 'testing_frequency', 'testing_method',
        'last_tested_at', 'next_test_due',
        'effectiveness_status', 'applicability_statement', 'is_applicable', 'client_scope',
    ];

    protected $casts = [
        'last_tested_at' => 'date',
        'next_test_due' => 'date',
        'is_applicable' => 'boolean',
        'client_scope' => 'array',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function frameworkControls(): BelongsToMany
    {
        return $this->belongsToMany(FrameworkControl::class, 'control_framework_mappings')
            ->withPivot('mapping_type', 'mapping_rationale')
            ->withTimestamps();
    }

    public function tests(): HasMany
    {
        return $this->hasMany(ControlTest::class);
    }

    public function evidence(): MorphMany
    {
        return $this->morphMany(EvidenceLink::class, 'linkable');
    }
}
