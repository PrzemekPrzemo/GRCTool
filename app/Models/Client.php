<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'industry', 'tier',
        'applicable_frameworks', 'contractual_security_requirements',
        'subprocessor_notification_required', 'notification_lead_time_days',
        'nda_signed_at', 'data_processing_agreement', 'dedicated_trust_portal_url',
        'authorized_contacts', 'is_active',
    ];

    protected $casts = [
        'applicable_frameworks' => 'array',
        'contractual_security_requirements' => 'array',
        'data_processing_agreement' => 'array',
        'authorized_contacts' => 'array',
        'subprocessor_notification_required' => 'boolean',
        'is_active' => 'boolean',
        'nda_signed_at' => 'datetime',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
