<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CertificateInventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'certificate_inventory';

    protected $fillable = [
        'code', 'common_name', 'san', 'issuer', 'cert_type', 'environment',
        'fingerprint_sha256', 'serial_number', 'issued_at', 'expires_at',
        'auto_renew', 'renewal_days_before', 'owner_id', 'managed_by',
        'asset_id', 'status', 'notes',
    ];

    protected $casts = [
        'san' => 'array',
        'issued_at' => 'date',
        'expires_at' => 'date',
        'auto_renew' => 'boolean',
    ];

    public const TYPE_LABELS = [
        'TLS' => 'TLS',
        'Code_Signing' => 'Podpisywanie kodu',
        'Internal_CA' => 'Wewnętrzna CA',
        'Client_Auth' => 'Uwierzytelnianie klienta',
        'S_MIME' => 'S/MIME',
    ];

    public const ENV_LABELS = [
        'production' => 'Produkcja',
        'staging' => 'Staging',
        'dev' => 'Dev',
        'internal' => 'Wewnętrzny',
    ];

    public const STATUS_LABELS = [
        'active' => 'Aktywny',
        'expired' => 'Wygasły',
        'revoked' => 'Unieważniony',
        'replaced' => 'Zastąpiony',
    ];

    protected static function booted(): void
    {
        static::saving(function (CertificateInventory $cert): void {
            if (! $cert->auto_renew
                && $cert->status === 'active'
                && $cert->expires_at !== null
                && $cert->expires_at->isPast()
            ) {
                $cert->status = 'expired';
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function daysUntilExpiry(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->expires_at, false);
    }

    public function urgencyLevel(): string
    {
        $days = $this->daysUntilExpiry();

        return match (true) {
            $days < 7 => 'critical',
            $days < 30 => 'high',
            $days < 90 => 'medium',
            default => 'ok',
        };
    }
}
