<?php

namespace App\Http\Controllers;

use App\Models\AuditEngagement;
use App\Models\Control;
use App\Models\EvidenceRequest;
use App\Models\Finding;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditEngagementController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->can('audit_engagement.view'), 403);

        $engagements = AuditEngagement::with('lead', 'findings', 'evidenceRequests')->orderByDesc('id')->paginate(25);

        return view('engagements.index', compact('engagements'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('audit_engagement.create'), 403);

        return view('engagements.form', $this->formData(new AuditEngagement));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('audit_engagement.create'), 403);

        $data = $this->validateEngagement($request);
        $e = AuditEngagement::create($data);

        return redirect()->route('engagements.show', $e)->with('status', "Audit engagement {$e->code} utworzony.");
    }

    public function show(AuditEngagement $engagement): View
    {
        abort_unless(auth()->user()->can('audit_engagement.view'), 403);

        $engagement->load(['lead', 'evidenceRequests.control', 'findings.owner']);

        return view('engagements.show', compact('engagement'));
    }

    public function edit(AuditEngagement $engagement): View
    {
        abort_unless(auth()->user()->can('audit_engagement.update'), 403);

        return view('engagements.form', $this->formData($engagement));
    }

    public function update(Request $request, AuditEngagement $engagement): RedirectResponse
    {
        abort_unless(auth()->user()->can('audit_engagement.update'), 403);

        $engagement->update($this->validateEngagement($request));

        return redirect()->route('engagements.show', $engagement)->with('status', 'Zaktualizowano.');
    }

    public function addEvidenceRequest(Request $request, AuditEngagement $engagement): RedirectResponse
    {
        abort_unless(auth()->user()->can('evidence.request'), 403);

        $data = $request->validate([
            'description' => ['required', 'string'],
            'sample_criteria' => ['nullable', 'string'],
            'control_id' => ['nullable', 'exists:controls,id'],
            'due_date' => ['nullable', 'date'],
        ]);

        EvidenceRequest::create([
            'engagement_id' => $engagement->id,
            'code' => 'ER-'.str_pad((string) ($engagement->evidenceRequests()->count() + 1), 3, '0', STR_PAD_LEFT),
            'requested_by' => auth()->id(),
            ...$data,
        ]);

        return back()->with('status', 'Evidence request dodany.');
    }

    public function addFinding(Request $request, AuditEngagement $engagement): RedirectResponse
    {
        abort_unless(auth()->user()->can('finding.create'), 403);

        $data = $request->validate([
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'severity' => ['required', 'in:Major,Minor,Observation,Recommendation'],
            'framework_reference' => ['nullable', 'string'],
            'linked_control_id' => ['nullable', 'exists:controls,id'],
            'discovered_at' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
        ]);

        $count = Finding::count() + 1;
        Finding::create([
            'code' => sprintf('F-%s-%04d', now()->format('Y'), $count),
            'engagement_id' => $engagement->id,
            'source' => 'External Audit',
            'status' => 'Open',
            ...$data,
        ]);

        return back()->with('status', 'Finding dodany.');
    }

    private function validateEngagement(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:255'],
            'framework' => ['nullable', 'string'],
            'type' => ['required', 'string'],
            'auditor_org' => ['nullable', 'string'],
            'audit_period_start' => ['nullable', 'date'],
            'audit_period_end' => ['nullable', 'date'],
            'fieldwork_start' => ['nullable', 'date'],
            'fieldwork_end' => ['nullable', 'date'],
            'scope_description' => ['nullable', 'string'],
            'status' => ['required', 'string'],
            'lead_id' => ['nullable', 'exists:users,id'],
        ]);
    }

    private function formData(AuditEngagement $e): array
    {
        return [
            'engagement' => $e,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'controls' => Control::orderBy('code')->get(['id', 'code', 'name']),
            'types' => ['External Cert', 'Surveillance', 'Recertification', 'Internal', 'Customer', 'Pentest', 'Regulatory'],
            'statuses' => ['Planning', 'Fieldwork', 'Reporting', 'Closed'],
            'frameworks' => ['ISO 27001:2022', 'SOC 2 Type II', 'NIS2', 'DORA', 'GDPR', 'TISAX', 'Customer'],
        ];
    }
}
