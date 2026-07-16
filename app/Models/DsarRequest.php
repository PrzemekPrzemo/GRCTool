<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DsarRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dsar_requests';

    protected $fillable = [
        'code', 'request_type', 'requester_name', 'requester_email',
        'requester_details', 'request_description', 'received_at',
        'deadline_at', 'deadline_extended', 'extended_deadline_at', 'extension_reason',
        'assigned_to', 'status', 'handling_notes',
        'identity_verified', 'identity_verification_notes',
        'completed_at', 'outcome', 'outcome_notes',
        'requester_notified', 'requester_notified_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'deadline_at' => 'datetime',
        'extended_deadline_at' => 'datetime',
        'completed_at' => 'datetime',
        'requester_notified_at' => 'datetime',
        'deadline_extended' => 'boolean',
        'identity_verified' => 'boolean',
        'requester_notified' => 'boolean',
    ];

    const REQUEST_TYPES = [
        'access' => 'Prawo dostępu (Art. 15)',
        'rectification' => 'Prawo do sprostowania (Art. 16)',
        'erasure' => 'Prawo do usunięcia (Art. 17)',
        'restriction' => 'Prawo do ograniczenia (Art. 18)',
        'portability' => 'Prawo do przenoszenia (Art. 20)',
        'objection' => 'Prawo sprzeciwu (Art. 21)',
        'withdraw_consent' => 'Wycofanie zgody (Art. 7 ust. 3)',
    ];

    const OUTCOMES = [
        'fulfilled' => 'Spełniono',
        'partially_fulfilled' => 'Częściowo spełniono',
        'rejected_no_data' => 'Odmowa — brak danych',
        'rejected_identity' => 'Odmowa — weryfikacja tożsamości',
        'rejected_legal' => 'Odmowa — podstawa prawna',
    ];

    protected static function booted(): void
    {
        static::creating(function (DsarRequest $dsar): void {
            if ($dsar->received_at && ! $dsar->deadline_at) {
                $dsar->deadline_at = Carbon::parse($dsar->received_at)->addDays(30);
            }
        });
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isOverdue(): bool
    {
        $deadline = $this->deadline_extended ? $this->extended_deadline_at : $this->deadline_at;

        return $deadline
            && $deadline->isPast()
            && ! in_array($this->status, ['completed', 'rejected', 'withdrawn']);
    }

    public function daysUntilDeadline(): ?int
    {
        $deadline = $this->deadline_extended ? $this->extended_deadline_at : $this->deadline_at;
        if (! $deadline) {
            return null;
        }

        return (int) now()->diffInDays($deadline, false);
    }

    public function requestTypeLabel(): string
    {
        return self::REQUEST_TYPES[$this->request_type] ?? ($this->request_type ?? '—');
    }

    public function outcomeLabel(): string
    {
        return self::OUTCOMES[$this->outcome] ?? ($this->outcome ?? '—');
    }
}
