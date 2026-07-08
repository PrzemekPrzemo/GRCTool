<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'business_unit_id',
        'manager_id',
        'is_external',
        'external_org',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'disabled_at' => 'datetime',
            'password' => 'hashed',
            'is_external' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function scopes(): HasMany
    {
        return $this->hasMany(UserScope::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    public function affectedIncidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'affected_user_id');
    }

    public function trainingCompletions(): HasMany
    {
        return $this->hasMany(UserTrainingCompletion::class);
    }

    public function hasMfaEnabled(): bool
    {
        return ! is_null($this->two_factor_confirmed_at);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isCiso(): bool
    {
        return $this->hasRole('ciso');
    }
}
