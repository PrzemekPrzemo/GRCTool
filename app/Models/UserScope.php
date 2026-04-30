<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserScope extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'scope_type', 'scope_id',
        'permissions_override', 'valid_from', 'valid_until',
    ];

    protected $casts = [
        'permissions_override' => 'array',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCurrentlyValid(): bool
    {
        $now = now();
        if ($this->valid_from && $this->valid_from->isAfter($now)) {
            return false;
        }
        if ($this->valid_until && $this->valid_until->isBefore($now)) {
            return false;
        }

        return true;
    }
}
