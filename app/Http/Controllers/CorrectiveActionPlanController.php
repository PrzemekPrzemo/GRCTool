<?php

namespace App\Http\Controllers;

use App\Models\CapAction;
use App\Models\CorrectiveActionPlan;
use App\Models\Finding;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CorrectiveActionPlanController extends Controller
{
    public function index(Request $request): View
    {
        $query = CorrectiveActionPlan::with(['approver'])
            ->withCount([
                'actions',
                'actions as overdue_count' => function ($q): void {
                    $q->whereNotIn('status', ['Completed', 'Cancelled'])
                      ->whereDate('due_date', '<', now());
                },
            ]);

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $caps = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        $stats = [
            'total'      => CorrectiveActionPlan::count(),
            'draft'      => CorrectiveActionPlan::where('status', 'Draft')->count(),
            'in_progress' => CorrectiveActionPlan::where('status', 'In Progress')->count(),
            'overdue'    => CapAction::whereNotIn('status', ['Completed', 'Cancelled'])
                                ->whereDate('due_date', '<', now())
                                ->count(),
        ];

        return view('cap.index', compact('caps', 'stats'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('cap.create'), 403);

        $findings = Finding::whereNotIn('status', ['Closed', 'Verified', 'Risk Accepted'])
            ->orderByDesc('created_at')
            ->get(['id', 'code', 'title', 'severity']);

        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('cap.form', compact('findings', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('cap.create'), 403);

        $validated = $request->validate([
            'title'                    => ['required', 'string', 'max:255'],
            'description'              => ['nullable', 'string'],
            'finding_ids'              => ['nullable', 'array'],
            'finding_ids.*'            => ['exists:findings,id'],
            'approver_id'              => ['nullable', 'exists:users,id'],
            'effectiveness_review_date' => ['nullable', 'date'],
            'status'                   => ['required', 'in:Draft,Approved,In Progress,Completed,Cancelled'],
        ]);

        $year  = now()->year;
        $count = CorrectiveActionPlan::whereYear('created_at', $year)->count() + 1;
        $code  = 'CAP-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);

        $cap = CorrectiveActionPlan::create(array_merge($validated, ['code' => $code]));

        AuditLogger::log('cap.created', $cap);

        return redirect()->route('cap.show', $cap)->with('status', "Plan {$cap->code} utworzony.");
    }

    public function show(CorrectiveActionPlan $cap): View
    {
        $cap->load(['actions.owner', 'approver']);

        $findings = Finding::whereIn('id', $cap->finding_ids ?? [])
            ->get(['id', 'code', 'title', 'severity', 'status']);

        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('cap.show', compact('cap', 'findings', 'users'));
    }

    public function edit(CorrectiveActionPlan $cap): View
    {
        abort_unless(auth()->user()->can('cap.update'), 403);

        $findings = Finding::whereNotIn('status', ['Closed', 'Verified', 'Risk Accepted'])
            ->orderByDesc('created_at')
            ->get(['id', 'code', 'title', 'severity']);

        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('cap.form', compact('cap', 'findings', 'users'));
    }

    public function update(Request $request, CorrectiveActionPlan $cap): RedirectResponse
    {
        abort_unless(auth()->user()->can('cap.update'), 403);

        $validated = $request->validate([
            'title'                    => ['required', 'string', 'max:255'],
            'description'              => ['nullable', 'string'],
            'finding_ids'              => ['nullable', 'array'],
            'finding_ids.*'            => ['exists:findings,id'],
            'approver_id'              => ['nullable', 'exists:users,id'],
            'effectiveness_review_date' => ['nullable', 'date'],
            'status'                   => ['required', 'in:Draft,Approved,In Progress,Completed,Cancelled'],
        ]);

        $cap->update($validated);

        AuditLogger::log('cap.updated', $cap);

        return redirect()->route('cap.show', $cap)->with('status', "Plan {$cap->code} zaktualizowany.");
    }

    public function approve(Request $request, CorrectiveActionPlan $cap): RedirectResponse
    {
        abort_unless(auth()->user()->can('cap.update'), 403);

        $cap->update([
            'status'      => 'Approved',
            'approved_at' => now(),
            'approver_id' => auth()->id(),
        ]);

        AuditLogger::log('cap.approved', $cap);

        return back()->with('status', "Plan {$cap->code} zatwierdzony.");
    }

    public function addAction(Request $request, CorrectiveActionPlan $cap): RedirectResponse
    {
        abort_unless(auth()->user()->can('cap.update'), 403);

        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'owner_id'         => ['required', 'exists:users,id'],
            'due_date'         => ['required', 'date'],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'status'           => ['required', 'in:Open,In Progress,Completed,Overdue,Cancelled'],
        ]);

        CapAction::create(array_merge($validated, ['cap_id' => $cap->id]));

        AuditLogger::log('cap.action_added', $cap);

        return back()->with('status', 'Akcja naprawcza dodana.');
    }

    public function updateAction(Request $request, CapAction $action): RedirectResponse
    {
        abort_unless(auth()->user()->can('cap.update'), 403);

        $validated = $request->validate([
            'status'           => ['required', 'in:Open,In Progress,Completed,Overdue,Cancelled'],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'completed_at'     => ['nullable', 'date'],
        ]);

        if ($validated['status'] === 'Completed' && empty($action->completed_at)) {
            $validated['completed_at']     = now()->toDateString();
            $validated['progress_percent'] = 100;
        }

        $action->update($validated);

        AuditLogger::log('cap.action_updated', $action->cap);

        return back()->with('status', 'Akcja zaktualizowana.');
    }
}
