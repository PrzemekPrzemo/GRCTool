<?php

namespace App\Http\Controllers;

use App\Models\SdlcProject;
use App\Models\SdlcSecurityGate;
use App\Models\SdlcThreatModel;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SdlcController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('sdlc.view'), 403);

        $query = SdlcProject::with('owner', 'gates');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($risk = $request->string('risk_level')->toString()) {
            $query->where('risk_level', $risk);
        }
        if ($team = $request->string('team')->toString()) {
            $query->where('team', 'like', "%{$team}%");
        }

        $projects = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('sdlc.index', compact('projects'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('sdlc.create'), 403);

        $users = User::orderBy('name')->get();

        return view('sdlc.form', ['project' => new SdlcProject, 'users' => $users]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('sdlc.create'), 403);

        $data = $this->validateProject($request);
        $data['code'] = SdlcProject::nextCode();

        $project = SdlcProject::create($data);
        AuditLogger::log('sdlc.created', $project);

        return redirect()->route('sdlc.show', $project)
            ->with('status', "Projekt {$project->code} utworzony.");
    }

    public function show(SdlcProject $sdlc): View
    {
        abort_unless(auth()->user()->can('sdlc.view'), 403);

        $sdlc->load('owner', 'threatModels.conductedBy', 'threatModels.reviewedBy', 'gates.conductedBy');
        $users = User::orderBy('name')->get();
        $phases = ['requirements', 'design', 'development', 'pre_release', 'production'];
        $gatesByPhase = $sdlc->gates->groupBy('phase');

        return view('sdlc.show', compact('sdlc', 'users', 'phases', 'gatesByPhase'));
    }

    public function edit(SdlcProject $sdlc): View
    {
        abort_unless(auth()->user()->can('sdlc.update'), 403);

        $users = User::orderBy('name')->get();

        return view('sdlc.form', ['project' => $sdlc, 'users' => $users]);
    }

    public function update(Request $request, SdlcProject $sdlc): RedirectResponse
    {
        abort_unless(auth()->user()->can('sdlc.update'), 403);

        $data = $this->validateProject($request);
        $sdlc->update($data);
        AuditLogger::log('sdlc.updated', $sdlc);

        return redirect()->route('sdlc.show', $sdlc)->with('status', 'Zaktualizowano projekt.');
    }

    public function addGate(Request $request, SdlcProject $sdlc): RedirectResponse
    {
        abort_unless(auth()->user()->can('sdlc.update'), 403);

        $data = $request->validate([
            'phase'          => ['required', 'in:requirements,design,development,pre_release,production'],
            'gate_type'      => ['required', 'in:threat_model,sast,dast,pentest,code_review,dependency_scan,secrets_scan,container_scan'],
            'status'         => ['required', 'in:pending,passed,failed,waived'],
            'tool'           => ['nullable', 'string', 'max:128'],
            'report_url'     => ['nullable', 'url', 'max:512'],
            'result_summary' => ['nullable', 'string'],
            'waiver_reason'  => ['nullable', 'string'],
            'critical_count' => ['nullable', 'integer', 'min:0'],
            'high_count'     => ['nullable', 'integer', 'min:0'],
            'medium_count'   => ['nullable', 'integer', 'min:0'],
            'low_count'      => ['nullable', 'integer', 'min:0'],
            'conducted_by'   => ['nullable', 'exists:users,id'],
            'conducted_at'   => ['nullable', 'date'],
        ]);

        $data['project_id'] = $sdlc->id;
        $gate = SdlcSecurityGate::create($data);
        AuditLogger::log('sdlc.gate_added', $gate);

        return back()->with('status', 'Bramka bezpieczeństwa dodana.');
    }

    public function updateGate(Request $request, SdlcProject $sdlc, SdlcSecurityGate $gate): RedirectResponse
    {
        abort_unless(auth()->user()->can('sdlc.update'), 403);
        abort_unless($gate->project_id === $sdlc->id, 404);

        $data = $request->validate([
            'phase'          => ['required', 'in:requirements,design,development,pre_release,production'],
            'gate_type'      => ['required', 'in:threat_model,sast,dast,pentest,code_review,dependency_scan,secrets_scan,container_scan'],
            'status'         => ['required', 'in:pending,passed,failed,waived'],
            'tool'           => ['nullable', 'string', 'max:128'],
            'report_url'     => ['nullable', 'url', 'max:512'],
            'result_summary' => ['nullable', 'string'],
            'waiver_reason'  => ['nullable', 'string'],
            'critical_count' => ['nullable', 'integer', 'min:0'],
            'high_count'     => ['nullable', 'integer', 'min:0'],
            'medium_count'   => ['nullable', 'integer', 'min:0'],
            'low_count'      => ['nullable', 'integer', 'min:0'],
            'conducted_by'   => ['nullable', 'exists:users,id'],
            'conducted_at'   => ['nullable', 'date'],
        ]);

        $gate->update($data);
        AuditLogger::log('sdlc.gate_updated', $gate);

        return back()->with('status', 'Bramka zaktualizowana.');
    }

    public function addThreatModel(Request $request, SdlcProject $sdlc): RedirectResponse
    {
        abort_unless(auth()->user()->can('sdlc.update'), 403);

        $data = $request->validate([
            'title'               => ['required', 'string', 'max:255'],
            'methodology'         => ['required', 'in:stride,pasta,linddun,other'],
            'status'              => ['required', 'in:draft,in_review,approved'],
            'threats_identified'  => ['nullable', 'integer', 'min:0'],
            'threats_mitigated'   => ['nullable', 'integer', 'min:0'],
            'document_url'        => ['nullable', 'url', 'max:512'],
            'notes'               => ['nullable', 'string'],
            'conducted_by'        => ['nullable', 'exists:users,id'],
            'reviewed_by'         => ['nullable', 'exists:users,id'],
            'reviewed_at'         => ['nullable', 'date'],
        ]);

        $data['project_id'] = $sdlc->id;
        $tm = SdlcThreatModel::create($data);
        AuditLogger::log('sdlc.threat_model_added', $tm);

        return back()->with('status', 'Model zagrożeń dodany.');
    }

    private function validateProject(Request $request): array
    {
        return $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'team'         => ['nullable', 'string', 'max:128'],
            'tech_stack'   => ['nullable', 'string', 'max:255'],
            'project_type' => ['required', 'in:webapp,api,mobile,infra,internal_tool'],
            'status'       => ['required', 'in:active,completed,archived'],
            'risk_level'   => ['nullable', 'in:low,medium,high,critical'],
            'owner_id'     => ['nullable', 'exists:users,id'],
            'repo_url'     => ['nullable', 'url', 'max:512'],
            'prod_url'     => ['nullable', 'url', 'max:512'],
            'description'  => ['nullable', 'string'],
            'notes'        => ['nullable', 'string'],
        ]);
    }
}
