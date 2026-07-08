<?php

namespace App\Services;

use App\Models\ComplianceAssessment;
use App\Models\ComplianceFramework;
use Illuminate\Support\Collection;

/**
 * Computes "% compliance" per framework from the latest completed assessment's
 * overall_score. There's no framework-level percentage stored anywhere else —
 * ComplianceAssessment.overall_score is per-assessment, so this rolls it up.
 */
class CompliancePostureCalculator
{
    /**
     * @return Collection<int, array{framework: ComplianceFramework, score: ?float, assessment: ?ComplianceAssessment, requirements_count: int}>
     */
    public function perFramework(): Collection
    {
        return ComplianceFramework::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (ComplianceFramework $fw): array {
                $latest = ComplianceAssessment::where('framework_id', $fw->id)
                    ->whereNotNull('overall_score')
                    ->orderByDesc('assessment_date')
                    ->first();

                return [
                    'framework' => $fw,
                    'score' => $latest !== null ? (float) $latest->overall_score : null,
                    'assessment' => $latest,
                    'requirements_count' => $fw->requirementsCount(),
                ];
            });
    }

    public function overallAverage(): ?float
    {
        $scores = $this->perFramework()->pluck('score')->filter(fn (?float $s) => $s !== null);

        return $scores->isEmpty() ? null : round($scores->avg(), 1);
    }
}
