<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'framework', 'issuer', 'certificate_number',
        'issued_at', 'valid_until', 'scope', 'evidence_id',
        'is_public', 'is_active',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'valid_until' => 'date',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function evidence(): BelongsTo
    {
        return $this->belongsTo(EvidenceObject::class, 'evidence_id');
    }

    public function isValid(): bool
    {
        return $this->is_active && $this->valid_until && $this->valid_until->isFuture();
    }

    public function daysUntilExpiry(): ?int
    {
        if (! $this->valid_until) {
            return null;
        }

        return (int) now()->diffInDays($this->valid_until, false);
    }
}
