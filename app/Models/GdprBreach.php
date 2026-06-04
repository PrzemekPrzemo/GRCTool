<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GdprBreach extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'gdpr_breaches';

    protected $fillable = [
        'code', 'title', 'description', 'breach_type',
        'occurred_at', 'discovered_at', 'contained_at',
        'incident_id',
        'data_categories_affected', 'special_categories_affected',
        'data_subjects_count', 'data_subjects_types',
        'risk_level', 'risk_description',
        'notification_required', 'data_subject_notification_required',
        'uodo_notified_at', 'uodo_notification_deadline',
        'uodo_reference_number', 'data_subjects_notified', 'data_subjects_notified_at',
        'remediation_actions', 'preventive_measures',
        'responsible_user_id', 'status', 'notes',
    ];

    protected $casts = [
        'occurred_at'                        => 'datetime',
        'discovered_at'                      => 'datetime',
        'contained_at'                       => 'datetime',
        'uodo_notified_at'                   => 'datetime',
        'uodo_notification_deadline'         => 'datetime',
        'data_subjects_notified_at'          => 'datetime',
        'data_categories_affected'           => 'array',
        'special_categories_affected'        => 'array',
        'data_subjects_types'                => 'array',
        'notification_required'              => 'boolean',
        'data_subject_notification_required' => 'boolean',
        'data_subjects_notified'             => 'boolean',
    ];

    const BREACH_TYPES = [
        'confidentiality' => 'Poufność — nieautoryzowany dostęp / ujawnienie',
        'integrity'       => 'Integralność — nieautoryzowana modyfikacja',
        'availability'    => 'Dostępność — utrata dostępu do danych',
    ];

    const RISK_LEVELS = [
        'low'       => 'Niskie',
        'medium'    => 'Średnie',
        'high'      => 'Wysokie',
        'very_high' => 'Bardzo wysokie',
    ];

    protected static function booted(): void
    {
        static::saving(function (GdprBreach $breach): void {
            if ($breach->discovered_at && $breach->notification_required && !$breach->uodo_notification_deadline) {
                $breach->uodo_notification_deadline = Carbon::parse($breach->discovered_at)->addHours(72);
            }
        });
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function isOverdue(): bool
    {
        return $this->notification_required
            && $this->uodo_notification_deadline
            && !$this->uodo_notified_at
            && $this->uodo_notification_deadline->isPast();
    }

    public function hoursUntilDeadline(): ?float
    {
        if (!$this->uodo_notification_deadline || $this->uodo_notified_at) {
            return null;
        }
        return round(now()->diffInMinutes($this->uodo_notification_deadline, false) / 60, 1);
    }

    public function riskLevelLabel(): string
    {
        return self::RISK_LEVELS[$this->risk_level] ?? ($this->risk_level ?? '—');
    }
}
