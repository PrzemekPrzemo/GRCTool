<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndicatorMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'indicator_id', 'measured_at', 'value', 'status',
        'dimensions', 'source_run_id', 'reported_by', 'notes',
    ];

    protected $casts = [
        'measured_at' => 'datetime',
        'value' => 'decimal:4',
        'dimensions' => 'array',
    ];

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(Indicator::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
