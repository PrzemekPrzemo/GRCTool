<?php

namespace App\Services;

use App\Models\AuditEngagement;
use App\Models\Control;
use App\Models\Finding;
use App\Models\Indicator;
use App\Models\Policy;
use App\Models\ReportInstance;
use App\Models\ReportTemplate;
use App\Models\Risk;
use App\Models\Subprocessor;
use App\Models\User;
use App\Models\Vulnerability;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportGenerator
{
    public function generate(ReportTemplate $template, array $params = []): ReportInstance
    {
        $periodStart = isset($params['period_start']) ? \Carbon\Carbon::parse($params['period_start']) : now()->startOfQuarter();
        $periodEnd = isset($params['period_end']) ? \Carbon\Carbon::parse($params['period_end']) : now()->endOfQuarter();

        $data = $this->fetchData($template, $periodStart, $periodEnd, $params);

        $code = sprintf('R-%s-%s-%s', $template->code, now()->format('YmdHi'), Str::upper(Str::random(4)));
        $watermark = "GENERATED {$code} · ".now()->format('Y-m-d H:i').' · CONFIDENTIAL';

        $pdf = Pdf::loadView($template->view_path, [
            'template' => $template,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'data' => $data,
            'watermark' => $watermark,
            'generatedAt' => now(),
            'generatedBy' => auth()->user()?->name ?? 'system',
            'reportCode' => $code,
        ]);

        $pdfBinary = $pdf->output();
        $filename = "$code.pdf";
        $path = "reports/$filename";
        Storage::disk('local')->put($path, $pdfBinary);

        $hash = hash('sha256', $pdfBinary);

        $instance = ReportInstance::create([
            'code' => $code,
            'template_id' => $template->id,
            'generated_by' => auth()->id(),
            'generated_at' => now(),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'scope' => $params['scope'] ?? null,
            'parameters' => $params,
            'output_files' => [['format' => 'pdf', 'path' => $path, 'sha256' => $hash, 'size' => strlen($pdfBinary)]],
            'watermark_text' => $watermark,
            'watermark_metadata' => ['hash' => $hash, 'sha256' => $hash],
            'classification' => $template->default_classification,
        ]);

        AuditLogger::log('report_generated', $instance, ['template' => $template->code, 'hash' => $hash]);

        return $instance;
    }

    private function fetchData(ReportTemplate $template, $start, $end, array $params): array
    {
        $client = $params['client_id'] ?? null;

        $base = [
            'risks_top10' => Risk::orderByDesc('residual_score')->take(10)->get(),
            'risks_total' => Risk::count(),
            'risks_over_appetite' => Risk::where('risk_appetite_breach', true)->count(),
            'controls_effective' => Control::where('effectiveness_status', 'Effective')->count(),
            'controls_total' => Control::count(),
            'vulns_open_critical' => Vulnerability::where('severity', 'Critical')->whereIn('status', ['Open', 'In Progress', 'Reopened'])->count(),
            'vulns_open_high' => Vulnerability::where('severity', 'High')->whereIn('status', ['Open', 'In Progress', 'Reopened'])->count(),
            'findings_open' => Finding::whereNotIn('status', ['Closed', 'Verified', 'Risk Accepted'])->count(),
            'engagements_active' => AuditEngagement::whereIn('status', ['Planning', 'Fieldwork', 'Reporting'])->get(),
            'indicators_executive' => Indicator::where('consumer_audience', 'Board')->where('is_active', true)
                ->with('latestMeasurement')->get(),
            'indicators_kci' => Indicator::where('type', 'KCI')->where('is_active', true)
                ->with('latestMeasurement')->get(),
        ];

        if ($template->code === 'ISO27001-AUDIT-PACK') {
            $base['controls_iso'] = Control::with('frameworkControls.frameworkVersion.framework')
                ->whereHas('frameworkControls.frameworkVersion.framework', fn ($q) => $q->where('code', 'ISO27001'))->get();
        }

        if ($template->code === 'CUSTOMER-SECURITY-PACK') {
            $base['policies'] = Policy::where('status', 'Active')->get();
            $base['subprocessors'] = Subprocessor::where('public_listing', true)->get();
        }

        return $base;
    }
}
