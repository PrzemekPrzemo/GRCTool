<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'auth_provider',
        'logged_in_at',
        'logged_out_at',
        'session_token',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
        'logged_out_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function duration(): string
    {
        if ($this->logged_out_at === null) {
            return 'aktywna';
        }

        return $this->logged_in_at->diffForHumans($this->logged_out_at, true);
    }

    public function isActive(): bool
    {
        return $this->logged_out_at === null;
    }
}
