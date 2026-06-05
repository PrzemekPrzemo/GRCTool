<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AuditEngagement;
use App\Models\BcpPlan;
use App\Models\CertificateInventory;
use App\Models\ComplianceException;
use App\Models\Control;
use App\Models\DsarRequest;
use App\Models\Finding;
use App\Models\GdprBreach;
use App\Models\Incident;
use App\Models\Indicator;
use App\Models\Risk;
use App\Models\Training;
use App\Models\UserTrainingCompletion;
use App\Models\Vulnerability;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'assets_total'          => Asset::count(),
            'assets_critical'       => Asset::where('criticality', 'Critical')->count(),

            'risks_open'            => Risk::whereNotIn('status', ['Closed', 'Accepted'])->count(),
            'risks_over_appetite'   => Risk::where('risk_appetite_breach', true)->count(),

            'controls_total'        => Control::count(),
            'controls_effective'    => Control::where('effectiveness_status', 'Effective')->count(),

            'vulns_open'            => Vulnerability::whereIn('status', ['Open', 'In Progress', 'Reopened'])->count(),
            'vulns_overdue'         => Vulnerability::whereIn('status', ['Open', 'In Progress', 'Reopened'])
                ->whereDate('due_date', '<', now())->count(),

            'findings_open'         => Finding::whereNotIn('status', ['Closed', 'Verified', 'Risk Accepted'])->count(),
            'engagements_active'    => AuditEngagement::whereIn('status', ['Planning', 'Fieldwork', 'Reporting'])->count(),

            'incidents_open'        => Incident::whereNotIn('status', ['Closed'])->count(),
            'incidents_p1_p2'       => Incident::whereIn('severity', ['P1', 'P2'])->whereNotIn('status', ['Closed'])->count(),

            'certs_expiring_30d'    => CertificateInventory::whereNull('revoked_at')
                ->whereDate('expires_at', '>=', now())
                ->whereDate('expires_at', '<=', now()->addDays(30))->count(),
            'certs_expired'         => CertificateInventory::whereNull('revoked_at')
                ->whereDate('expires_at', '<', now())->count(),

            'gdpr_breach_overdue'   => GdprBreach::whereNull('uodo_notified_at')
                ->whereDate('discovered_at', '<', now()->subHours(72))->count(),

            'dsar_overdue'          => DsarRequest::whereNotIn('status', ['Completed', 'Rejected'])
                ->whereDate('deadline_at', '<', now())->count(),

            'bcp_untested'          => BcpPlan::where('status', 'approved')
                ->whereDoesntHave('tests')->count(),

            'exceptions_pending'    => ComplianceException::where('status', 'pending_approval')->count(),

            'trainings_completion'  => $this->trainingCompletionPct(),
        ];

        $heatmap  = $this->buildRiskHeatmap();
        $topRisks = Risk::orderByDesc('residual_score')->limit(10)->get();

        $indicators = Indicator::where('is_active', true)
            ->with(['latestMeasurement'])
            ->orderBy('type')
            ->limit(12)
            ->get();

        $alerts = $this->buildAlerts($stats);

        return view('dashboard', compact('stats', 'heatmap', 'topRisks', 'indicators', 'alerts'));
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

    private function trainingCompletionPct(): int
    {
        $total = UserTrainingCompletion::count();
        if ($total === 0) {
            return 0;
        }

        $passed = UserTrainingCompletion::where('status', 'passed')->count();

        return (int)round($passed / $total * 100);
    }

    private function buildAlerts(array $stats): array
    {
        $alerts = [];

        if ($stats['incidents_p1_p2'] > 0) {
            $alerts[] = ['level' => 'critical', 'msg' => "Aktywne incydenty P1/P2: {$stats['incidents_p1_p2']} — wymagają natychmiastowej reakcji", 'href' => route('incidents.index')];
        }
        if ($stats['certs_expired'] > 0) {
            $alerts[] = ['level' => 'critical', 'msg' => "Wygasłe certyfikaty: {$stats['certs_expired']} — usługi mogą być zagrożone", 'href' => route('certificates.index')];
        }
        if ($stats['gdpr_breach_overdue'] > 0) {
            $alerts[] = ['level' => 'critical', 'msg' => "Naruszenia RODO bez notyfikacji UODO po 72h: {$stats['gdpr_breach_overdue']}", 'href' => route('gdpr-breaches.index')];
        }
        if ($stats['dsar_overdue'] > 0) {
            $alerts[] = ['level' => 'warning', 'msg' => "Wnioski DSAR po terminie: {$stats['dsar_overdue']}", 'href' => route('dsar.index')];
        }
        if ($stats['certs_expiring_30d'] > 0) {
            $alerts[] = ['level' => 'warning', 'msg' => "Certyfikaty wygasające w ciągu 30 dni: {$stats['certs_expiring_30d']}", 'href' => route('certificates.index')];
        }
        if ($stats['exceptions_pending'] > 0) {
            $alerts[] = ['level' => 'info', 'msg' => "Wyjątki oczekujące na zatwierdzenie: {$stats['exceptions_pending']}", 'href' => route('exceptions.index')];
        }
        if ($stats['vulns_overdue'] > 0) {
            $alerts[] = ['level' => 'warning', 'msg' => "Podatności po SLA: {$stats['vulns_overdue']}", 'href' => route('vulnerabilities.index')];
        }

        return $alerts;
    }
}
