<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Indicator extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'type', 'description', 'formula',
        'data_source', 'connector_ref', 'unit', 'target_value',
        'green_threshold', 'amber_threshold', 'red_threshold', 'direction',
        'frequency', 'owner_id', 'consumer_audience',
        'linked_controls', 'linked_risks', 'linked_assets', 'framework_mappings',
        'is_active',
    ];

    protected $casts = [
        'target_value' => 'decimal:4',
        'green_threshold' => 'decimal:4',
        'amber_threshold' => 'decimal:4',
        'red_threshold' => 'decimal:4',
        'linked_controls' => 'array',
        'linked_risks' => 'array',
        'linked_assets' => 'array',
        'framework_mappings' => 'array',
        'is_active' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(IndicatorMeasurement::class)->orderByDesc('measured_at');
    }

    public function latestMeasurement()
    {
        return $this->hasOne(IndicatorMeasurement::class)->latestOfMany('measured_at');
    }

    /**
     * Klasyfikuje wartość względem progów green/amber/red i kierunku.
     */
    public function classify(float $value): string
    {
        $green = (float) $this->green_threshold;
        $amber = (float) $this->amber_threshold;
        $red = (float) $this->red_threshold;

        if ($this->direction === 'higher_is_better') {
            return match (true) {
                $value >= $green => 'green',
                $value >= $amber => 'amber',
                default => 'red',
            };
        }

        // lower_is_better
        return match (true) {
            $value <= $green => 'green',
            $value <= $amber => 'amber',
            default => 'red',
        };
    }
}
