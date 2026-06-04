<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTrainingCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_id', 'user_id', 'completed_at', 'expires_at',
        'score', 'certificate_ref', 'status', 'waived_by', 'waive_reason',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function waivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waived_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
