<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EvidenceObject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'title', 'description', 'original_filename', 'storage_path',
        'mime_type', 'size_bytes', 'sha256', 'classification', 'tags',
        'valid_from', 'valid_until', 'retention_until',
        'is_immutable', 'uploaded_by', 'client_id',
        'source', 'external_provider', 'external_file_id', 'external_url', 'external_synced_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'retention_until' => 'date',
        'is_immutable' => 'boolean',
        'external_synced_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (EvidenceObject $obj): void {
            if (empty($obj->uuid)) {
                $obj->uuid = (string) Str::uuid();
            }
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(EvidenceLink::class, 'evidence_id');
    }

    public function isExternal(): bool
    {
        return $this->source !== 'upload';
    }

    public function isExpiring(int $days = 30): bool
    {
        return $this->valid_until !== null
            && $this->valid_until->isFuture()
            && $this->valid_until->lte(now()->addDays($days));
    }

    public function isStale(): bool
    {
        return $this->valid_until !== null && $this->valid_until->isPast();
    }

    public function expiryStatus(): string
    {
        if ($this->valid_until === null) {
            return 'no_expiry';
        }
        if ($this->valid_until->isPast()) {
            return 'expired';
        }
        if ($this->valid_until->lte(now()->addDays(30))) {
            return 'expiring_soon';
        }

        return 'valid';
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereNotNull('valid_until')
            ->where('valid_until', '>=', now())
            ->where('valid_until', '<=', now()->addDays($days));
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('valid_until')->where('valid_until', '<', now());
    }
}
