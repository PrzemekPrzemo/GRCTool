<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Training extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'title', 'description', 'type', 'required_for_roles',
        'owner_id', 'frequency', 'expiry_days', 'is_mandatory', 'is_active',
    ];

    protected $casts = [
        'required_for_roles' => 'array',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    public const TYPE_LABELS = [
        'security_awareness' => 'Świadomość bezp.',
        'gdpr' => 'GDPR/RODO',
        'role_specific' => 'Rola-specyficzne',
        'technical' => 'Techniczne',
        'onboarding' => 'Onboarding',
    ];

    public const FREQUENCY_LABELS = [
        'annual' => 'Roczne',
        'semi_annual' => 'Półroczne',
        'on_hire' => 'Przy zatrudnieniu',
        'on_change' => 'Przy zmianie',
        'one_time' => 'Jednorazowe',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function completions(): HasMany
    {
        return $this->hasMany(UserTrainingCompletion::class);
    }

    /**
     * Returns completion rate as percentage (completed/assigned * 100).
     */
    public function completionRate(): float
    {
        $total = $this->completions()->count();
        if ($total === 0) {
            return 0.0;
        }
        $completed = $this->completions()->where('status', 'completed')->count();

        return round(($completed / $total) * 100, 1);
    }
}
