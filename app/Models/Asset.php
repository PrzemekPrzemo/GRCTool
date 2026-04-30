<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'description', 'type', 'environment',
        'confidentiality_impact', 'integrity_impact', 'availability_impact', 'criticality',
        'data_classification', 'data_categories',
        'owner_id', 'custodian_id', 'business_unit_id', 'client_id', 'project_id',
        'tags', 'external_ids', 'lifecycle_status',
        'last_reviewed_at', 'next_review_due',
    ];

    protected $casts = [
        'data_categories' => 'array',
        'tags' => 'array',
        'external_ids' => 'array',
        'last_reviewed_at' => 'date',
        'next_review_due' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (Asset $asset): void {
            $asset->criticality = self::computeCriticality(
                (int) $asset->confidentiality_impact,
                (int) $asset->integrity_impact,
                (int) $asset->availability_impact,
            );
        });
    }

    /**
     * Criticality = max(C,I,A) jako baseline.
     * Reguła upgrade: jeśli wszystkie składowe >= 3 → upgrade do najwyższego poziomu.
     * Skala wynikowa: Low(1), Medium(2), High(3), Critical(4).
     */
    public static function computeCriticality(int $c, int $i, int $a): string
    {
        $max = max($c, $i, $a);
        if ($c >= 3 && $i >= 3 && $a >= 3) {
            $max = 4;
        }

        return match (true) {
            $max >= 4 => 'Critical',
            $max === 3 => 'High',
            $max === 2 => 'Medium',
            default => 'Low',
        };
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function custodian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'custodian_id');
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'asset_dependencies', 'parent_asset_id', 'child_asset_id')
            ->withPivot('relation_type', 'description')
            ->withTimestamps();
    }

    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'asset_dependencies', 'child_asset_id', 'parent_asset_id')
            ->withPivot('relation_type', 'description')
            ->withTimestamps();
    }

    public function vulnerabilities(): BelongsToMany
    {
        return $this->belongsToMany(Vulnerability::class, 'vulnerability_assets')
            ->withPivot('affected_component', 'status', 'first_seen', 'last_seen', 'closed_at')
            ->withTimestamps();
    }

    public function evidence(): MorphMany
    {
        return $this->morphMany(EvidenceLink::class, 'linkable');
    }
}
