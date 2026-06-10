<?php

namespace App\Http\Controllers;

use App\Models\Control;
use App\Models\Finding;
use App\Models\Incident;
use App\Models\Risk;
use App\Models\Vulnerability;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Eksportuje ryzyka do CSV.
     */
    public function risks(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()->can('risk.view'), 403);

        $risks = Risk::with('owner')->get();

        $headers = [
            'ID', 'Kod', 'Tytuł', 'Kategoria', 'Właściciel',
            'Inherent Score', 'Residual Score', 'Status',
            'Termin przeglądu', 'Utworzony',
        ];

        return response()->streamDownload(function () use ($risks, $headers): void {
            $output = fopen('php://output', 'w');

            // BOM dla poprawnego wyświetlania polskich znaków w Excel
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, $headers);

            foreach ($risks as $risk) {
                fputcsv($output, [
                    $risk->id,
                    $risk->code,
                    $risk->title,
                    trim($risk->category_l1 . ' / ' . $risk->category_l2, ' /'),
                    $risk->owner?->name ?? '—',
                    $risk->inherent_score,
                    $risk->residual_score,
                    $risk->status,
                    $risk->next_review_date?->format('Y-m-d') ?? '—',
                    $risk->created_at?->format('Y-m-d'),
                ]);
            }

            fclose($output);
        }, 'ryzyka_' . now()->format('Ymd_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Eksportuje kontrolki do CSV.
     */
    public function controls(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()->can('control.view'), 403);

        $controls = Control::with('owner', 'frameworkControls')->get();

        $headers = [
            'ID', 'Kod', 'Nazwa', 'Typ', 'Właściciel',
            'Status', 'Framework', 'Częstotliwość testowania', 'Ostatni test',
        ];

        return response()->streamDownload(function () use ($controls, $headers): void {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, $headers);

            foreach ($controls as $control) {
                $frameworks = $control->frameworkControls
                    ->pluck('reference')
                    ->filter()
                    ->implode(', ');

                fputcsv($output, [
                    $control->id,
                    $control->code,
                    $control->name,
                    $control->control_type,
                    $control->owner?->name ?? '—',
                    $control->effectiveness_status,
                    $frameworks ?: '—',
                    $control->testing_frequency,
                    $control->last_tested_at?->format('Y-m-d') ?? '—',
                ]);
            }

            fclose($output);
        }, 'kontrolki_' . now()->format('Ymd_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Eksportuje podatności do CSV.
     */
    public function vulnerabilities(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()->can('vulnerability.view'), 403);

        $vulnerabilities = Vulnerability::with(['owner', 'assets'])->get();

        $headers = [
            'ID', 'CVE', 'Tytuł', 'Severity', 'CVSS',
            'Status', 'Asset', 'Właściciel', 'Due Date', 'Odkryty',
        ];

        return response()->streamDownload(function () use ($vulnerabilities, $headers): void {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, $headers);

            foreach ($vulnerabilities as $vuln) {
                $assetNames = $vuln->assets->pluck('name')->filter()->implode(', ');

                fputcsv($output, [
                    $vuln->id,
                    $vuln->cve_id ?? '—',
                    $vuln->title,
                    $vuln->severity,
                    $vuln->cvss_score ?? '—',
                    $vuln->status,
                    $assetNames ?: '—',
                    $vuln->owner?->name ?? '—',
                    $vuln->due_date?->format('Y-m-d') ?? '—',
                    $vuln->discovered_at?->format('Y-m-d') ?? '—',
                ]);
            }

            fclose($output);
        }, 'podatnosci_' . now()->format('Ymd_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Eksportuje findingi do CSV.
     */
    public function findings(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()->can('finding.view'), 403);

        $findings = Finding::with(['owner', 'engagement'])->get();

        $headers = [
            'ID', 'Kod', 'Tytuł', 'Źródło', 'Severity',
            'Status', 'Engagement', 'Właściciel', 'Termin', 'Odkryty',
        ];

        return response()->streamDownload(function () use ($findings, $headers): void {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, $headers);

            foreach ($findings as $finding) {
                fputcsv($output, [
                    $finding->id,
                    $finding->code,
                    $finding->title,
                    $finding->source,
                    $finding->severity,
                    $finding->status,
                    $finding->engagement?->code ?? '—',
                    $finding->owner?->name ?? '—',
                    $finding->due_date?->format('Y-m-d') ?? '—',
                    $finding->discovered_at?->format('Y-m-d') ?? '—',
                ]);
            }

            fclose($output);
        }, 'findingi_' . now()->format('Ymd_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Eksportuje incydenty do CSV.
     */
    public function incidents(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()->can('incident.view'), 403);

        $incidents = Incident::with('owner')->get();

        $headers = [
            'ID', 'Kod', 'Tytuł', 'Severity', 'Status',
            'Źródło', 'Właściciel', 'Is Breach', 'ENISA Score',
            'Wykryty', 'Rozwiązany',
        ];

        return response()->streamDownload(function () use ($incidents, $headers): void {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, $headers);

            foreach ($incidents as $incident) {
                fputcsv($output, [
                    $incident->id,
                    $incident->code,
                    $incident->title,
                    $incident->severity,
                    $incident->status,
                    $incident->source ?? '—',
                    $incident->owner?->name ?? '—',
                    $incident->is_breach ? 'Tak' : 'Nie',
                    $incident->enisa_severity_score ?? '—',
                    $incident->detected_at?->format('Y-m-d') ?? '—',
                    $incident->resolved_at?->format('Y-m-d') ?? '—',
                ]);
            }

            fclose($output);
        }, 'incydenty_' . now()->format('Ymd_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
