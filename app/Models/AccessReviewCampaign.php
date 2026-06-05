<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccessReviewCampaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'title', 'description',
        'scope', 'scope_value', 'status',
        'owner_id', 'due_date',
        'review_period_start', 'review_period_end',
        'total_items', 'reviewed_items', 'revoked_items',
        'notes', 'completed_at',
    ];

    protected $casts = [
        'due_date'            => 'date',
        'review_period_start' => 'date',
        'review_period_end'   => 'date',
        'completed_at'        => 'datetime',
        'total_items'         => 'integer',
        'reviewed_items'      => 'integer',
        'revoked_items'       => 'integer',
        'owner_id'            => 'integer',
    ];

    public const SCOPE_LABELS = [
        'all_systems' => 'Wszystkie systemy',
        'department'  => 'Departament',
        'system'      => 'System',
        'role'        => 'Rola',
    ];

    public const STATUS_LABELS = [
        'draft'     => 'Szkic',
        'active'    => 'Aktywna',
        'completed' => 'Zakończona',
        'cancelled' => 'Anulowana',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AccessReviewItem::class, 'campaign_id');
    }

    public function pendingItems(): HasMany
    {
        return $this->hasMany(AccessReviewItem::class, 'campaign_id')->where('status', 'pending');
    }

    public function progressPct(): int
    {
        if ($this->total_items === 0) {
            return 0;
        }

        return (int) round($this->reviewed_items / $this->total_items * 100);
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && $this->status === 'active';
    }

    public static function nextCode(): string
    {
        $year = now()->year;
        $last = static::withTrashed()->where('code', 'like', "AR-{$year}-%")->count();

        return sprintf('AR-%d-%04d', $year, $last + 1);
    }
}
