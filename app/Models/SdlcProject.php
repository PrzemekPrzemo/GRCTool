<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SdlcProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'description', 'team', 'tech_stack',
        'project_type', 'status', 'risk_level', 'owner_id',
        'repo_url', 'prod_url', 'notes',
    ];

    protected $casts = [
        'owner_id' => 'integer',
    ];

    public const TYPE_LABELS = [
        'webapp' => 'Web App',
        'api' => 'API',
        'mobile' => 'Mobile',
        'infra' => 'Infrastruktura',
        'internal_tool' => 'Narzędzie wewnętrzne',
    ];

    public const RISK_COLORS = [
        'low' => 'emerald',
        'medium' => 'amber',
        'high' => 'orange',
        'critical' => 'red',
    ];

    public const RISK_LABELS = [
        'low' => 'Niskie',
        'medium' => 'Średnie',
        'high' => 'Wysokie',
        'critical' => 'Krytyczne',
    ];

    public const STATUS_LABELS = [
        'active' => 'Aktywny',
        'completed' => 'Zakończony',
        'archived' => 'Zarchiwizowany',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function threatModels(): HasMany
    {
        return $this->hasMany(SdlcThreatModel::class, 'project_id');
    }

    public function gates(): HasMany
    {
        return $this->hasMany(SdlcSecurityGate::class, 'project_id');
    }

    public function gatesByPhase(): array
    {
        return $this->gates->groupBy('phase')->toArray();
    }

    public function overallGateStatus(): string
    {
        $gates = $this->gates;
        if ($gates->where('status', 'failed')->count() > 0) {
            return 'failed';
        }
        if ($gates->where('status', 'pending')->count() > 0) {
            return 'pending';
        }
        if ($gates->where('status', 'waived')->count() > 0) {
            return 'waived';
        }

        return 'passed';
    }

    public static function nextCode(): string
    {
        $year = now()->year;
        $last = static::withTrashed()->where('code', 'like', "SDLC-{$year}-%")->count();

        return sprintf('SDLC-%d-%04d', $year, $last + 1);
    }
}
