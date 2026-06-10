<?php

namespace App\Http\Controllers;

use App\Models\AuditEngagement;
use App\Models\Control;
use App\Models\Finding;
use App\Models\Risk;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FindingController extends Controller
{
    public function index(Request $request): View
    {
        $q = Finding::query()->with('engagement', 'control', 'risk', 'owner');
        if ($s = $request->string('status')->toString()) {
            $q->where('status', $s);
        }
        if ($sev = $request->string('severity')->toString()) {
            $q->where('severity', $sev);
        }
        $findings = $q->orderByDesc('discovered_at')->paginate(50)->withQueryString();

        return view('findings.index', compact('findings'));
    }

    public function show(Finding $finding): View
    {
        $finding->load('engagement', 'control', 'risk', 'owner', 'verifier');

        return view('findings.show', compact('finding'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('finding.create'), 403);

        $engagements = AuditEngagement::whereIn('status', ['Planning', 'Fieldwork', 'Reporting'])
            ->orderByDesc('created_at')->get();
        $controls = Control::orderBy('code')->get(['id', 'code', 'name']);
        $risks = Risk::whereNotIn('status', ['Closed'])->orderBy('code')->get(['id', 'code', 'title']);
        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('findings.form', compact('engagements', 'controls', 'risks', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('finding.create'), 403);

        $data = $request->validate([
            'title'               => 'required|max:255',
            'description'         => 'nullable',
            'source'              => 'required|in:External Audit,Internal Audit,Pentest,Customer Review,Self-assessment,Regulator,Bug Bounty',
            'severity'            => 'required|in:Major,Minor,Observation,Recommendation',
            'engagement_id'       => 'nullable|exists:audit_engagements,id',
            'linked_control_id'   => 'nullable|exists:controls,id',
            'linked_risk_id'      => 'nullable|exists:risks,id',
            'owner_id'            => 'required|exists:users,id',
            'discovered_at'       => 'required|date',
            'due_date'            => 'nullable|date|after_or_equal:discovered_at',
            'framework_reference' => 'nullable|max:255',
        ]);

        $quarter = 'Q' . ceil(now()->month / 3);
        $year = now()->year;
        $count = Finding::whereYear('created_at', $year)->count() + 1;
        $data['code'] = sprintf('F-%d-%s-%05d', $year, $quarter, $count);
        $data['status'] = 'Open';

        $finding = Finding::create($data);
        AuditLogger::log('finding.created', $finding);
        if ($data['severity'] === 'Major') {
            \App\Services\SlackNotifier::criticalFindingCreated($finding);
        }

        return redirect()->route('findings.show', $finding)->with('success', 'Finding dodany.');
    }

    public function edit(Finding $finding): View
    {
        abort_unless(auth()->user()->can('finding.update'), 403);

        $engagements = AuditEngagement::whereIn('status', ['Planning', 'Fieldwork', 'Reporting'])
            ->orWhere('id', $finding->engagement_id)
            ->orderByDesc('created_at')->get();
        $controls = Control::orderBy('code')->get(['id', 'code', 'name']);
        $risks = Risk::whereNotIn('status', ['Closed'])->orWhere('id', $finding->linked_risk_id)->orderBy('code')->get(['id', 'code', 'title']);
        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('findings.form', compact('finding', 'engagements', 'controls', 'risks', 'users'));
    }

    public function update(Request $request, Finding $finding): RedirectResponse
    {
        abort_unless(auth()->user()->can('finding.update'), 403);

        $data = $request->validate([
            'title'               => 'required|max:255',
            'description'         => 'nullable',
            'source'              => 'required|in:External Audit,Internal Audit,Pentest,Customer Review,Self-assessment,Regulator,Bug Bounty',
            'severity'            => 'required|in:Major,Minor,Observation,Recommendation',
            'engagement_id'       => 'nullable|exists:audit_engagements,id',
            'linked_control_id'   => 'nullable|exists:controls,id',
            'linked_risk_id'      => 'nullable|exists:risks,id',
            'owner_id'            => 'required|exists:users,id',
            'discovered_at'       => 'required|date',
            'due_date'            => 'nullable|date|after_or_equal:discovered_at',
            'framework_reference' => 'nullable|max:255',
            'status'              => 'required|in:Open,In Progress,Remediated,Verified,Closed,Risk Accepted,Disputed',
        ]);

        $finding->update($data);
        AuditLogger::log('finding.updated', $finding);

        return redirect()->route('findings.show', $finding)->with('success', 'Finding zaktualizowany.');
    }

    public function close(Request $request, Finding $finding): RedirectResponse
    {
        $data = $request->validate([
            'evidence_of_closure' => ['required', 'string'],
        ]);

        $finding->update([
            'status' => 'Verified',
            'evidence_of_closure' => $data['evidence_of_closure'],
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'closed_at' => now()->toDateString(),
        ]);

        return back()->with('status', 'Finding zweryfikowany i zamknięty.');
    }
}
