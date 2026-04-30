<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'risk_id', 'version_number', 'snapshot', 'diff', 'changed_by', 'change_reason', 'changed_at',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'diff' => 'array',
        'changed_at' => 'datetime',
    ];

    public function risk(): BelongsTo
    {
        return $this->belongsTo(Risk::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
