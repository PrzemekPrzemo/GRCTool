<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AuditEngagement;
use App\Models\BcpPlan;
use App\Models\CertificateInventory;
use App\Models\ComplianceAssessment;
use App\Models\ComplianceException;
use App\Models\Control;
use App\Models\DsarRequest;
use App\Models\EvidenceObject;
use App\Models\Finding;
use App\Models\GdprBreach;
use App\Models\Incident;
use App\Models\Indicator;
use App\Models\Risk;
use App\Models\Training;
use App\Models\UserTrainingCompletion;
use App\Models\Vulnerability;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = $this->buildStats();

        $heatmap    = $this->buildRiskHeatmap();
        $topRisks   = Risk::orderByDesc('residual_score')->limit(10)->get();
        $indicators = Indicator::where('is_active', true)
            ->with(['latestMeasurement'])
            ->orderBy('type')
            ->limit(12)
            ->get();

        $trends = $this->buildTrends();

        return view('dashboard', compact('stats', 'heatmap', 'topRisks', 'indicators', 'trends'));
    }

    public function exportPdf(): View
    {
        abort_unless(auth()->user()->can('report.generate'), 403);

        $stats    = $this->buildStats();
        $heatmap  = $this->buildRiskHeatmap();
        $topRisks = Risk::orderByDesc('residual_score')->limit(10)->get();
        $trends   = $this->buildTrends();

        return view('dashboard.export', compact('stats', 'heatmap', 'topRisks', 'trends'));
    }

    // ── private helpers ────────────────────────────────────────────────────────

    private function buildStats(): array
    {
        return [
            'assets_total' => Asset::count(),
            'assets_critical' => Asset::where('criticality', 'Critical')->count(),
            'risks_open' => Risk::whereNotIn('status', ['Closed', 'Accepted'])->count(),
            'risks_over_appetite' => Risk::where('risk_appetite_breach', true)->count(),
            'controls_total' => Control::count(),
            'controls_effective' => Control::where('effectiveness_status', 'Effective')->count(),
            'vulns_open' => Vulnerability::whereIn('status', ['Open', 'In Progress', 'Reopened'])->count(),
            'vulns_overdue' => Vulnerability::whereIn('status', ['Open', 'In Progress', 'Reopened'])
                ->whereDate('due_date', '<', now())->count(),
            'findings_open' => Finding::whereNotIn('status', ['Closed', 'Verified', 'Risk Accepted'])->count(),
            'engagements_active' => AuditEngagement::whereIn('status', ['Planning', 'Fieldwork', 'Reporting'])->count(),
            'incidents_open_p1_p2' => Incident::whereIn('severity', ['Critical', 'High'])->whereNotIn('status', ['Closed'])->count(),
            'certs_expiring_30d' => CertificateInventory::where('status', 'active')->whereDate('expires_at', '<=', now()->addDays(30))->whereDate('expires_at', '>=', now())->count(),
            'certs_expired' => CertificateInventory::where('status', 'active')->whereDate('expires_at', '<', now())->count(),
            'gdpr_breach_overdue' => GdprBreach::where('notification_required', true)->whereNull('uodo_notified_at')->where('uodo_notification_deadline', '<', now())->count(),
            'dsar_overdue' => DsarRequest::whereNotIn('status', ['completed', 'closed'])->where(function ($q) {
                $q->where('deadline_at', '<', now())->orWhere('extended_deadline_at', '<', now());
            })->count(),
            'bcp_untested' => BcpPlan::where('status', 'active')->whereDoesntHave('tests')->count(),
            'exceptions_pending' => ComplianceException::where('status', 'pending_approval')->count(),
            'trainings_completion_pct' => (function () {
                $total = UserTrainingCompletion::whereIn('status', ['pending', 'completed', 'expired'])->count();
                $done  = UserTrainingCompletion::where('status', 'completed')->count();

                return $total > 0 ? round($done / $total * 100) : 0;
            })(),
            'evidence_expiring_30d' => EvidenceObject::expiringSoon(30)->count(),
            'rtp_actions_overdue'   => \App\Models\RtpAction::whereNotIn('status', ['Completed', 'Cancelled'])->whereDate('due_date', '<', now())->count(),
        ];
    }

    private function buildRiskHeatmap(): array
    {
        $matrix = [];
        for ($l = 5; $l >= 1; $l--) {
            for ($i = 1; $i <= 5; $i++) {
                $matrix[$l][$i] = Risk::where('residual_likelihood', $l)
                    ->where('residual_impact', $i)
                    ->whereNotIn('status', ['Closed'])
                    ->count();
            }
        }

        return $matrix;
    }

    private function buildTrends(): array
    {
        $months = collect(range(11, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i)->copy());

        $risksNew = $months->map(fn ($m) => [
            'x' => $m->format('Y-m'),
            'y' => Risk::whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->count(),
        ]);

        $vulnsNew = $months->map(fn ($m) => [
            'x' => $m->format('Y-m'),
            'y' => Vulnerability::whereYear('discovered_at', $m->year)->whereMonth('discovered_at', $m->month)->count(),
        ]);

        $vulnsClosed = $months->map(fn ($m) => [
            'x' => $m->format('Y-m'),
            'y' => Vulnerability::whereYear('closed_at', $m->year)->whereMonth('closed_at', $m->month)->count(),
        ]);

        $incidentsNew = $months->map(fn ($m) => [
            'x' => $m->format('Y-m'),
            'y' => Incident::whereYear('detected_at', $m->year)->whereMonth('detected_at', $m->month)->count(),
        ]);

        $complianceScores = ComplianceAssessment::where('status', 'completed')
            ->whereNotNull('overall_score')
            ->orderBy('assessment_date')
            ->get(['assessment_date', 'overall_score'])
            ->map(fn ($a) => [
                'x' => $a->assessment_date?->format('Y-m-d') ?? '',
                'y' => (float) $a->overall_score,
            ]);

        return compact('risksNew', 'vulnsNew', 'vulnsClosed', 'incidentsNew', 'complianceScores');
    }
}
