<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Append-only audit log.
 * Polityka aplikacji zabrania UPDATE/DELETE — observer rzuca wyjątek.
 */
class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'occurred_at', 'user_id', 'user_email', 'action',
        'subject_type', 'subject_id', 'subject_code',
        'changes', 'context', 'ip_address', 'user_agent', 'integrity_hash',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'changes' => 'array',
        'context' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new \RuntimeException('AuditLog is immutable. Updates are not permitted.');
        });
        static::deleting(function (): void {
            throw new \RuntimeException('AuditLog is immutable. Deletes are not permitted.');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
