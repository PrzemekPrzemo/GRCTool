<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SdlcSecurityGate extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'phase', 'gate_type', 'status',
        'result_summary', 'waiver_reason',
        'critical_count', 'high_count', 'medium_count', 'low_count',
        'tool', 'report_url', 'conducted_by', 'conducted_at',
    ];

    protected $casts = [
        'critical_count' => 'integer',
        'high_count' => 'integer',
        'medium_count' => 'integer',
        'low_count' => 'integer',
        'conducted_by' => 'integer',
        'conducted_at' => 'datetime',
    ];

    public const PHASE_LABELS = [
        'requirements' => 'Wymagania',
        'design' => 'Projektowanie',
        'development' => 'Implementacja',
        'pre_release' => 'Pre-release',
        'production' => 'Produkcja',
    ];

    public const GATE_TYPE_LABELS = [
        'threat_model' => 'Threat Model',
        'sast' => 'SAST',
        'dast' => 'DAST',
        'pentest' => 'Pentest',
        'code_review' => 'Code Review',
        'dependency_scan' => 'Dependency Scan',
        'secrets_scan' => 'Secrets Scan',
        'container_scan' => 'Container Scan',
    ];

    public const STATUS_LABELS = [
        'pending' => 'Oczekuje',
        'passed' => 'Zaliczony',
        'failed' => 'Niezaliczony',
        'waived' => 'Zwolniony',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(SdlcProject::class, 'project_id');
    }

    public function conductedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }
}
