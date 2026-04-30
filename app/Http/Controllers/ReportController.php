<?php

namespace App\Http\Controllers;

use App\Models\ReportInstance;
use App\Models\ReportTemplate;
use App\Services\AuditLogger;
use App\Services\ReportGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private ReportGenerator $generator) {}

    public function index(): View
    {
        $templates = ReportTemplate::where('is_active', true)->get();
        $instances = ReportInstance::with('template', 'generator')->orderByDesc('generated_at')->paginate(25);

        return view('reports.index', compact('templates', 'instances'));
    }

    public function generate(Request $request, ReportTemplate $template): RedirectResponse
    {
        $params = $request->validate([
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date'],
            'client_id' => ['nullable', 'exists:clients,id'],
        ]);

        $instance = $this->generator->generate($template, $params);

        return redirect()->route('reports.show', $instance)->with('status', "Raport {$instance->code} wygenerowany.");
    }

    public function show(ReportInstance $report): View
    {
        $report->load('template', 'generator');

        return view('reports.show', compact('report'));
    }

    public function download(ReportInstance $report): StreamedResponse
    {
        $first = collect($report->output_files)->first();
        if (! $first || ! Storage::disk('local')->exists($first['path'])) {
            abort(404);
        }

        $log = $report->distribution_log ?? [];
        $log[] = [
            'user' => auth()->user()?->email,
            'ip' => request()->ip(),
            'ts' => now()->toIso8601String(),
        ];
        $report->update(['distribution_log' => $log]);

        AuditLogger::log('report_downloaded', $report);

        return Storage::disk('local')->download($first['path'], "{$report->code}.pdf");
    }

    public function revoke(Request $request, ReportInstance $report): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string']]);
        $report->update([
            'revoked' => true,
            'revoked_at' => now(),
            'revoked_reason' => $data['reason'],
        ]);
        AuditLogger::log('report_revoked', $report, ['reason' => $data['reason']]);

        return back()->with('status', 'Raport oznaczony jako revoked.');
    }
}
