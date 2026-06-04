<?php

namespace App\Http\Controllers;

use App\Models\BcpPlan;
use App\Models\BcpTest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BcpController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->can('bcp.view'), 403);

        $plans = BcpPlan::with('owner')
            ->orderByDesc('created_at')
            ->paginate(25);

        // Eager load latest test for each plan
        $plans->each(fn ($p) => $p->setRelation('latestTestRelation', $p->latestTest()));

        return view('bcp.index', compact('plans'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('bcp.create'), 403);

        $users = User::orderBy('name')->get(['id', 'name']);

        return view('bcp.form', ['plan' => new BcpPlan, 'users' => $users]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('bcp.create'), 403);

        $data = $this->validatePlan($request);

        $year = now()->format('Y');
        $count = BcpPlan::withTrashed()->whereYear('created_at', $year)->count() + 1;
        $data['code'] = sprintf('BCP-%s-%04d', $year, $count);

        $plan = BcpPlan::create($data);
        AuditLogger::log('bcp.created', $plan);

        return redirect()->route('bcp.show', $plan)->with('status', "Plan {$plan->code} utworzony.");
    }

    public function show(BcpPlan $bcp): View
    {
        abort_unless(auth()->user()->can('bcp.view'), 403);

        $bcp->load('owner', 'approver');
        $tests = $bcp->tests()->with('conductor')->orderByDesc('tested_at')->get();
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('bcp.show', compact('bcp', 'tests', 'users'));
    }

    public function edit(BcpPlan $bcp): View
    {
        abort_unless(auth()->user()->can('bcp.update'), 403);

        $users = User::orderBy('name')->get(['id', 'name']);

        return view('bcp.form', ['plan' => $bcp, 'users' => $users]);
    }

    public function update(Request $request, BcpPlan $bcp): RedirectResponse
    {
        abort_unless(auth()->user()->can('bcp.update'), 403);

        $data = $this->validatePlan($request);
        $bcp->update($data);
        AuditLogger::log('bcp.updated', $bcp);

        return redirect()->route('bcp.show', $bcp)->with('status', 'Zaktualizowano.');
    }

    public function approve(Request $request, BcpPlan $bcp): RedirectResponse
    {
        abort_unless(auth()->user()->can('bcp.update'), 403);

        $bcp->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'status' => 'active',
        ]);
        AuditLogger::log('bcp.approved', $bcp);

        return back()->with('status', 'Plan BCP zatwierdzony i aktywny.');
    }

    public function addTest(Request $request, BcpPlan $bcp): RedirectResponse
    {
        abort_unless(auth()->user()->can('bcp.update'), 403);

        $data = $request->validate([
            'test_type' => ['required', 'in:tabletop,walkthrough,simulation,partial_interruption,full_interruption'],
            'tested_at' => ['required', 'date'],
            'conducted_by' => ['nullable', 'exists:users,id'],
            'result' => ['required', 'in:pass,pass_with_gaps,fail'],
            'gaps_identified' => ['nullable', 'string'],
            'actions_taken' => ['nullable', 'string'],
            'test_scenario' => ['nullable', 'string'],
            'next_test_due' => ['nullable', 'date'],
        ]);

        $count = BcpTest::count() + 1;
        $data['bcp_plan_id'] = $bcp->id;
        $data['code'] = sprintf('BCPT-%04d', $count);

        BcpTest::create($data);
        AuditLogger::log('bcp.test_recorded', $bcp);

        return back()->with('status', 'Test BCP zarejestrowany.');
    }

    private function validatePlan(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'plan_type' => ['required', 'in:bcp,dr,coop,crisis'],
            'scope' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'rto_hours' => ['nullable', 'numeric', 'min:0'],
            'rpo_minutes' => ['nullable', 'integer', 'min:0'],
            'mtd_hours' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,active,under_review,retired'],
            'version' => ['required', 'integer', 'min:1'],
            'last_reviewed_at' => ['nullable', 'date'],
            'next_review_due' => ['nullable', 'date'],
        ]);
    }
}
