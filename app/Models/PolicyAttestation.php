<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyAttestation extends Model
{
    protected $fillable = [
        'policy_id', 'user_id', 'policy_version', 'attested_at', 'ip_address',
    ];

    protected $casts = [
        'attested_at' => 'datetime',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
