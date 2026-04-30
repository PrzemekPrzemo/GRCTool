<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FrameworkVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'framework_id', 'version', 'published_at', 'effective_from', 'is_current', 'metadata',
    ];

    protected $casts = [
        'published_at' => 'date',
        'effective_from' => 'date',
        'is_current' => 'boolean',
        'metadata' => 'array',
    ];

    public function framework(): BelongsTo
    {
        return $this->belongsTo(Framework::class);
    }

    public function controls(): HasMany
    {
        return $this->hasMany(FrameworkControl::class);
    }
}
