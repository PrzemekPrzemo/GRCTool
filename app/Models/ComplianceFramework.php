<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceFramework extends Model
{
    protected $fillable = [
        'code', 'name', 'short_name', 'version', 'issuer',
        'region', 'description', 'is_active', 'is_custom', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_custom' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function domains(): HasMany
    {
        return $this->hasMany(ComplianceDomain::class, 'framework_id')->orderBy('sort_order');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(ComplianceAssessment::class, 'framework_id');
    }

    public function requirementsCount(): int
    {
        return ComplianceRequirement::whereHas('domain', function ($q): void {
            $q->where('framework_id', $this->id);
        })->count();
    }

    public static function nextAssessmentCode(int $frameworkId): string
    {
        $year = now()->year;
        $last = ComplianceAssessment::where('code', 'like', "CA-{$year}-%")->count();

        return sprintf('CA-%d-%04d', $year, $last + 1);
    }
}
