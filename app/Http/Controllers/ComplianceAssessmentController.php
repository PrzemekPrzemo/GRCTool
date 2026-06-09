<?php

namespace App\Http\Controllers;

use App\Models\ComplianceAssessment;
use App\Models\ComplianceFramework;
use App\Models\ComplianceResponse;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ComplianceAssessmentController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Frameworks catalogue
    // ─────────────────────────────────────────────────────────────────────────

    public function frameworks(): View
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $frameworks = ComplianceFramework::where('is_active', true)
            ->withCount('assessments')
            ->orderBy('sort_order')
            ->get()
            ->map(function (ComplianceFramework $fw): ComplianceFramework {
                $fw->requirements_count_cached = $fw->requirementsCount();
                $fw->active_assessments        = $fw->assessments()
                    ->whereIn('status', ['draft', 'in_progress'])
                    ->count();

                return $fw;
            });

        return view('compliance.frameworks', compact('frameworks'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Assessments CRUD
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $query = ComplianceAssessment::with(['framework', 'conductedBy'])
            ->orderByDesc('created_at');

        if ($fwId = $request->integer('framework_id')) {
            $query->where('framework_id', $fwId);
        }
        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $assessments = $query->paginate(25)->withQueryString();
        $frameworks  = ComplianceFramework::where('is_active', true)->orderBy('sort_order')->get();

        return view('compliance.index', compact('assessments', 'frameworks'));
    }

    public function create(Request $request): View
    {
        abort_unless(auth()->user()->can('compliance.create'), 403);

        $frameworks = ComplianceFramework::where('is_active', true)->orderBy('sort_order')->get();
        $users      = User::orderBy('name')->get();
        $selected   = $request->integer('framework') ?: null;

        return view('compliance.create', compact('frameworks', 'users', 'selected'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('compliance.create'), 403);

        $data = $request->validate([
            'framework_id'    => ['required', 'exists:compliance_frameworks,id'],
            'title'           => ['required', 'string', 'max:256'],
            'scope'           => ['nullable', 'string'],
            'conducted_by'    => ['nullable', 'exists:users,id'],
            'assessment_date' => ['nullable', 'date'],
            'notes'           => ['nullable', 'string'],
        ]);

        $data['code']   = ComplianceAssessment::nextCode();
        $data['status'] = 'draft';

        $assessment = ComplianceAssessment::create($data);
        AuditLogger::log('compliance.assessment_created', $assessment);

        return redirect()->route('compliance.show', $assessment)
            ->with('status', "Ocena {$assessment->code} została utworzona.");
    }

    public function show(ComplianceAssessment $compliance): View
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $compliance->load([
            'framework.domains.requirements',
            'conductedBy',
            'reviewedBy',
        ]);

        // Index responses by requirement_id for quick lookup
        $responses = $compliance->responses()->get()->keyBy('requirement_id');

        return view('compliance.show', compact('compliance', 'responses'));
    }

    public function edit(ComplianceAssessment $compliance): View
    {
        abort_unless(auth()->user()->can('compliance.update'), 403);

        $frameworks = ComplianceFramework::where('is_active', true)->orderBy('sort_order')->get();
        $users      = User::orderBy('name')->get();

        return view('compliance.create', [
            'assessment' => $compliance,
            'frameworks' => $frameworks,
            'users'      => $users,
            'selected'   => $compliance->framework_id,
        ]);
    }

    public function update(Request $request, ComplianceAssessment $compliance): RedirectResponse
    {
        abort_unless(auth()->user()->can('compliance.update'), 403);

        $data = $request->validate([
            'framework_id'    => ['required', 'exists:compliance_frameworks,id'],
            'title'           => ['required', 'string', 'max:256'],
            'scope'           => ['nullable', 'string'],
            'conducted_by'    => ['nullable', 'exists:users,id'],
            'assessment_date' => ['nullable', 'date'],
            'notes'           => ['nullable', 'string'],
        ]);

        $compliance->update($data);
        AuditLogger::log('compliance.assessment_updated', $compliance);

        return redirect()->route('compliance.show', $compliance)
            ->with('status', 'Ocena zaktualizowana.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Respond
    // ─────────────────────────────────────────────────────────────────────────

    public function showRespond(ComplianceAssessment $assessment): View
    {
        abort_unless(auth()->user()->can('compliance.update'), 403);

        $assessment->load([
            'framework.domains.requirements',
        ]);

        $responses = $assessment->responses()->get()->keyBy('requirement_id');

        return view('compliance.respond', compact('assessment', 'responses'));
    }

    public function respond(ComplianceAssessment $assessment, Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('compliance.update'), 403);

        $data = $request->validate([
            'responses'                          => ['required', 'array'],
            'responses.*.status'                 => ['required', 'string', 'in:compliant,partial,non_compliant,not_applicable,not_assessed'],
            'responses.*.evidence'               => ['nullable', 'string'],
            'responses.*.gap_description'        => ['nullable', 'string'],
            'responses.*.remediation_plan'       => ['nullable', 'string'],
            'responses.*.priority'               => ['nullable', 'in:high,medium,low'],
            'responses.*.target_date'            => ['nullable', 'date'],
        ]);

        $userId = auth()->id();
        $now    = now();

        foreach ($data['responses'] as $reqId => $row) {
            ComplianceResponse::updateOrCreate(
                ['assessment_id' => $assessment->id, 'requirement_id' => (int) $reqId],
                [
                    'status'           => $row['status'],
                    'evidence'         => $row['evidence'] ?? null,
                    'gap_description'  => $row['gap_description'] ?? null,
                    'remediation_plan' => $row['remediation_plan'] ?? null,
                    'priority'         => $row['priority'] ?? null,
                    'target_date'      => $row['target_date'] ?? null,
                    'responded_by'     => $userId,
                    'responded_at'     => $now,
                ]
            );
        }

        // Auto-advance to in_progress
        if ($assessment->status === 'draft') {
            $assessment->update(['status' => 'in_progress']);
        }

        $assessment->recalculateScore();
        AuditLogger::log('compliance.responses_saved', $assessment);

        return redirect()->route('compliance.show', $assessment)
            ->with('status', 'Odpowiedzi zostały zapisane.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // State transitions
    // ─────────────────────────────────────────────────────────────────────────

    public function complete(ComplianceAssessment $assessment): RedirectResponse
    {
        abort_unless(auth()->user()->can('compliance.update'), 403);

        $assessment->recalculateScore();
        $assessment->update([
            'status'      => 'completed',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        AuditLogger::log('compliance.assessment_completed', $assessment);

        return back()->with('status', "Ocena {$assessment->code} zakończona. Wynik: {$assessment->overall_score}%");
    }

    public function publish(ComplianceAssessment $assessment): RedirectResponse
    {
        abort_unless(auth()->user()->can('compliance.update'), 403);

        if ($assessment->status !== 'completed') {
            return back()->with('error', 'Ocena musi być zakończona przed opublikowaniem SoA.');
        }

        $assessment->update(['is_published' => true]);
        AuditLogger::log('compliance.soa_published', $assessment);

        return back()->with('status', "Deklaracja stosowalności (SoA) dla {$assessment->code} została opublikowana.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Exports
    // ─────────────────────────────────────────────────────────────────────────

    public function exportCsv(ComplianceAssessment $assessment): Response
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $assessment->load(['framework.domains.requirements']);
        $responses = $assessment->responses()->get()->keyBy('requirement_id');

        $statusLabels = [
            'compliant'      => 'Zgodne',
            'partial'        => 'Częściowe',
            'non_compliant'  => 'Niezgodne',
            'not_applicable' => 'Nie dotyczy',
            'not_assessed'   => 'Nie oceniono',
        ];
        $priorityLabels = [
            'high'   => 'Wysoki',
            'medium' => 'Średni',
            'low'    => 'Niski',
        ];

        $rows = [];
        $rows[] = ['Code', 'Domain', 'Requirement', 'Status', 'Evidence', 'Gap', 'Remediation', 'Priority', 'Target Date'];

        foreach ($assessment->framework->domains as $domain) {
            foreach ($domain->requirements as $req) {
                $resp      = $responses->get($req->id);
                $rows[]    = [
                    $req->code,
                    $domain->name,
                    $req->name,
                    $statusLabels[$resp?->status ?? 'not_assessed'] ?? ($resp?->status ?? 'Nie oceniono'),
                    $resp?->evidence ?? '',
                    $resp?->gap_description ?? '',
                    $resp?->remediation_plan ?? '',
                    isset($resp?->priority) ? ($priorityLabels[$resp->priority] ?? $resp->priority) : '',
                    $resp?->target_date?->format('Y-m-d') ?? '',
                ];
            }
        }

        $csv    = "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(function (string $cell): string {
                // Neutralise spreadsheet formula injection (CSV injection)
                if (preg_match('/^[=+\-@\t\r]/', $cell)) {
                    $cell = "'" . $cell;
                }
                $cell = str_replace('"', '""', $cell);

                return '"' . $cell . '"';
            }, $row)) . "\r\n";
        }

        // Strip non-printable / newline chars from filename to prevent CRLF header injection
        $safeCode = preg_replace('/[^A-Za-z0-9_\-]/', '_', $assessment->code);
        $filename = "compliance_{$safeCode}_" . now()->format('Ymd') . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportSoa(ComplianceAssessment $assessment): View
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $assessment->load([
            'framework.domains.requirements',
            'conductedBy',
            'reviewedBy',
        ]);

        $responses = $assessment->responses()->get()->keyBy('requirement_id');

        $total         = $assessment->compliant_count + $assessment->partial_count
                       + $assessment->non_compliant_count + $assessment->not_assessed_count;
        $totalWithNa   = $total + $assessment->na_count;

        return view('compliance.exports.soa', compact('assessment', 'responses', 'totalWithNa'));
    }

    public function exportGap(ComplianceAssessment $assessment): View
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $assessment->load([
            'framework.domains.requirements',
            'conductedBy',
        ]);

        $responses = $assessment->responses()->get()->keyBy('requirement_id');

        // Collect only non_compliant + partial, sorted
        $gaps = collect();

        foreach ($assessment->framework->domains as $domain) {
            foreach ($domain->requirements as $req) {
                $resp = $responses->get($req->id);
                if (! $resp || ! in_array($resp->status, ['non_compliant', 'partial'])) {
                    continue;
                }
                $gaps->push([
                    'domain'          => $domain,
                    'requirement'     => $req,
                    'response'        => $resp,
                ]);
            }
        }

        // Sort: non_compliant first, then partial; within each group priority high > medium > low > null
        $priorityOrder = ['high' => 0, 'medium' => 1, 'low' => 2, null => 3];
        $gaps = $gaps->sort(function (array $a, array $b) use ($priorityOrder): int {
            $statusA = $a['response']->status === 'non_compliant' ? 0 : 1;
            $statusB = $b['response']->status === 'non_compliant' ? 0 : 1;
            if ($statusA !== $statusB) {
                return $statusA <=> $statusB;
            }

            $prioA = $priorityOrder[$a['response']->priority] ?? 3;
            $prioB = $priorityOrder[$b['response']->priority] ?? 3;

            return $prioA <=> $prioB;
        })->values();

        $nonCompliantCount = $gaps->filter(fn ($g) => $g['response']->status === 'non_compliant')->count();
        $partialCount      = $gaps->filter(fn ($g) => $g['response']->status === 'partial')->count();

        return view('compliance.exports.gap', compact(
            'assessment', 'gaps', 'nonCompliantCount', 'partialCount'
        ));
    }
}
