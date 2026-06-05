<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceAssessment extends Model
{
    protected $fillable = [
        'code', 'framework_id', 'title', 'scope', 'conducted_by',
        'assessment_date', 'status', 'overall_score',
        'compliant_count', 'partial_count', 'non_compliant_count',
        'na_count', 'not_assessed_count', 'is_published', 'notes',
        'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'assessment_date'   => 'date',
        'is_published'      => 'boolean',
        'overall_score'     => 'decimal:2',
        'reviewed_at'       => 'datetime',
        'compliant_count'   => 'integer',
        'partial_count'     => 'integer',
        'non_compliant_count' => 'integer',
        'na_count'          => 'integer',
        'not_assessed_count' => 'integer',
    ];

    public const STATUS_LABELS = [
        'draft'       => 'Szkic',
        'in_progress' => 'W toku',
        'completed'   => 'Zakończona',
        'archived'    => 'Archiwum',
    ];

    public function framework(): BelongsTo
    {
        return $this->belongsTo(ComplianceFramework::class, 'framework_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ComplianceResponse::class, 'assessment_id');
    }

    public function conductedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function recalculateScore(): void
    {
        $counts = $this->responses()
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $compliant    = (int) ($counts['compliant'] ?? 0);
        $partial      = (int) ($counts['partial'] ?? 0);
        $nonCompliant = (int) ($counts['non_compliant'] ?? 0);
        $na           = (int) ($counts['not_applicable'] ?? 0);
        $notAssessed  = (int) ($counts['not_assessed'] ?? 0);

        $total      = $compliant + $partial + $nonCompliant + $notAssessed;
        $assessable = $total; // excludes na

        $score = null;
        if ($assessable > 0) {
            $score = round(($compliant + $partial * 0.5) / $assessable * 100, 2);
        }

        $this->update([
            'compliant_count'     => $compliant,
            'partial_count'       => $partial,
            'non_compliant_count' => $nonCompliant,
            'na_count'            => $na,
            'not_assessed_count'  => $notAssessed,
            'overall_score'       => $score,
        ]);
    }

    public static function nextCode(): string
    {
        $year = now()->year;
        $last = static::where('code', 'like', "CA-{$year}-%")->count();

        return sprintf('CA-%d-%04d', $year, $last + 1);
    }
}
