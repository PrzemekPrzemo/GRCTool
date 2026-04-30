<?php

namespace App\Models;

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
    ];

    protected $casts = [
        'tags' => 'array',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'retention_until' => 'date',
        'is_immutable' => 'boolean',
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
}
