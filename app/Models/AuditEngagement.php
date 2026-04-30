<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditEngagement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'framework', 'type', 'auditor_org', 'auditor_contacts',
        'audit_period_start', 'audit_period_end', 'fieldwork_start', 'fieldwork_end',
        'scope_description', 'scope_assets', 'scope_controls', 'status',
        'lead_id', 'final_report_url', 'certificate_url',
        'cert_valid_from', 'cert_valid_until',
    ];

    protected $casts = [
        'auditor_contacts' => 'array',
        'scope_assets' => 'array',
        'scope_controls' => 'array',
        'audit_period_start' => 'date',
        'audit_period_end' => 'date',
        'fieldwork_start' => 'date',
        'fieldwork_end' => 'date',
        'cert_valid_from' => 'date',
        'cert_valid_until' => 'date',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_id');
    }

    public function evidenceRequests(): HasMany
    {
        return $this->hasMany(EvidenceRequest::class, 'engagement_id');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class, 'engagement_id');
    }
}
