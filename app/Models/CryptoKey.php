<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CryptoKey extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'key_type', 'algorithm', 'key_size',
        'storage_location', 'key_id', 'rotation_days',
        'last_rotated_at', 'next_rotation_due', 'purpose',
        'owner_id', 'is_active', 'notes',
    ];

    protected $casts = [
        'last_rotated_at' => 'date',
        'next_rotation_due' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (CryptoKey $key): void {
            if ($key->isDirty('last_rotated_at') && $key->last_rotated_at !== null) {
                $key->next_rotation_due = $key->last_rotated_at->addDays($key->rotation_days);
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function isOverdueForRotation(): bool
    {
        return $this->next_rotation_due !== null && $this->next_rotation_due->isPast();
    }
}
