<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incident extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'title', 'description', 'severity', 'status', 'source',
        'occurred_at', 'detected_at', 'acknowledged_at', 'contained_at', 'resolved_at',
        'owner_id', 'is_breach',
        'affected_clients', 'affected_assets', 'linked_risks', 'linked_controls',
        'post_mortem', 'estimated_cost_eur',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'detected_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'contained_at' => 'datetime',
        'resolved_at' => 'datetime',
        'is_breach' => 'boolean',
        'affected_clients' => 'array',
        'affected_assets' => 'array',
        'linked_risks' => 'array',
        'linked_controls' => 'array',
        'estimated_cost_eur' => 'decimal:2',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function mttdMinutes(): ?int
    {
        if (! $this->occurred_at || ! $this->detected_at) {
            return null;
        }

        return (int) $this->occurred_at->diffInMinutes($this->detected_at);
    }

    public function mttaMinutes(): ?int
    {
        if (! $this->detected_at || ! $this->acknowledged_at) {
            return null;
        }

        return (int) $this->detected_at->diffInMinutes($this->acknowledged_at);
    }

    public function mttrHours(): ?int
    {
        if (! $this->detected_at || ! $this->resolved_at) {
            return null;
        }

        return (int) $this->detected_at->diffInHours($this->resolved_at);
    }
}
