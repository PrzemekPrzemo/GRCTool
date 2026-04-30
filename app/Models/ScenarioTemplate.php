<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScenarioTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'description', 'category_l1', 'category_l2',
        'default_threat_actors', 'default_mitre_techniques',
        'default_likelihood_pert', 'default_impact_pert',
        'typical_assets_affected', 'recommended_controls', 'data_sources', 'is_active',
    ];

    protected $casts = [
        'default_threat_actors' => 'array',
        'default_mitre_techniques' => 'array',
        'default_likelihood_pert' => 'array',
        'default_impact_pert' => 'array',
        'typical_assets_affected' => 'array',
        'recommended_controls' => 'array',
        'data_sources' => 'array',
        'is_active' => 'boolean',
    ];

    public function risks(): HasMany
    {
        return $this->hasMany(Risk::class);
    }
}
