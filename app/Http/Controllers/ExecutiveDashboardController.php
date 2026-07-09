<?php

namespace App\Http\Controllers;

use App\Models\ComplianceCalendarTask;
use App\Models\ComplianceException;
use App\Models\ComplianceGap;
use App\Models\Indicator;
use App\Models\Risk;
use Illuminate\View\View;

/**
 * Zunifikowany dashboard CSO/Zarządu — odpowiednik kwartalnego raportu
 * zarządu (REG-015 zadanie Q-05, szablon CLT-410): status RAG KPI/KRI,
 * otwarte luki compliance krytyczne/wysokie, wyjątki wygasające w ciągu
 * 14 dni, zadania kalendarza compliance w bieżącym cyklu, top ryzyka.
 */
class ExecutiveDashboardController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $indicators = Indicator::where('is_active', true)
            ->whereIn('type', ['KPI', 'KRI'])
            ->with('latestMeasurement')
            ->get();

        $ragCounts = ['KPI' => ['green' => 0, 'amber' => 0, 'red' => 0, 'none' => 0], 'KRI' => ['green' => 0, 'amber' => 0, 'red' => 0, 'none' => 0]];
        foreach ($indicators as $indicator) {
            $status = $indicator->latestMeasurement->status ?? 'none';
            $ragCounts[$indicator->type][$status] = ($ragCounts[$indicator->type][$status] ?? 0) + 1;
        }

        $attentionIndicators = $indicators
            ->filter(fn (Indicator $i) => in_array($i->latestMeasurement->status ?? null, ['amber', 'red'], true))
            ->sortBy(fn (Indicator $i) => $i->latestMeasurement->status === 'red' ? 0 : 1)
            ->values();

        $criticalGaps = ComplianceGap::whereIn('severity', ['critical', 'high'])
            ->where('status', '!=', 'closed')
            ->orderByRaw("case severity when 'critical' then 0 else 1 end")
            ->orderBy('target_date')
            ->get();

        $expiringExceptions = ComplianceException::where('status', 'approved')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays(14)])
            ->orderBy('expires_at')
            ->with('approver')
            ->get();

        $calendarTasksThisCycle = ComplianceCalendarTask::all()
            ->filter(fn (ComplianceCalendarTask $t) => $this->isDueThisCycle($t))
            ->sortBy('ref')
            ->values();

        $topRisks = Risk::whereNotIn('status', ['Closed', 'Accepted'])
            ->with('owner')
            ->orderByDesc('residual_score')
            ->limit(8)
            ->get();

        return view('dashboard.executive', compact(
            'ragCounts', 'attentionIndicators', 'criticalGaps',
            'expiringExceptions', 'calendarTasksThisCycle', 'topRisks',
        ));
    }

    /**
     * REG-015 nie ma pola due_date ani realnie wypełnianego statusu
     * wykonania (kolumny Status Q1-Q4 w źródle to pusty szablon) — jedyne
     * realne pole określające kiedy zadanie jest aktualne to "months"
     * (np. "Every month", "Jan, Apr, Jul, Oct", "Q3 (Jul–Sep)"). Stąd
     * przybliżenie "w bieżącym cyklu" zamiast nieobsługiwanego "overdue".
     */
    private function isDueThisCycle(ComplianceCalendarTask $task): bool
    {
        if ($task->frequency === 'Event-triggered' || $task->months === null) {
            return false;
        }
        if (str_contains($task->months, 'Every month')) {
            return true;
        }
        if (str_contains($task->months, now()->format('M'))) {
            return true;
        }
        if (preg_match_all('/Q([1-4])/', $task->months, $matches)) {
            $currentQuarter = (string) ceil(now()->month / 3);

            return in_array($currentQuarter, $matches[1], true);
        }

        return false;
    }
}
