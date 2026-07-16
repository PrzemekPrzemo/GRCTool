<?php

namespace App\Http\Controllers;

use App\Models\RiskTreatmentPlan;
use App\Models\RtpAction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiskTreatmentPlanController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('risk.view'), 403);

        $query = RiskTreatmentPlan::with(['risk', 'actions.owner', 'approver'])
            ->withCount(['actions', 'actions as overdue_count' => function ($q): void {
                $q->whereNotIn('status', ['Completed', 'Cancelled'])
                    ->whereDate('due_date', '<', now());
            }]);

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($request->boolean('overdue_only')) {
            $query->whereHas('actions', function ($q): void {
                $q->whereNotIn('status', ['Completed', 'Cancelled'])
                    ->whereDate('due_date', '<', now());
            });
        }

        $plans = $query->orderByDesc('overdue_count')->orderByDesc('created_at')->paginate(25)->withQueryString();

        $overdueActions = RtpAction::with(['plan.risk', 'owner'])
            ->whereNotIn('status', ['Completed', 'Cancelled'])
            ->whereDate('due_date', '<', now())
            ->orderBy('due_date')
            ->limit(20)
            ->get();

        $totalStats = [
            'plans_active' => RiskTreatmentPlan::whereNotIn('status', ['Completed', 'Cancelled', 'Rejected'])->count(),
            'actions_overdue' => RtpAction::whereNotIn('status', ['Completed', 'Cancelled'])->whereDate('due_date', '<', now())->count(),
            'actions_in_progress' => RtpAction::where('status', 'In Progress')->count(),
            'actions_completed' => RtpAction::where('status', 'Completed')->count(),
        ];

        return view('risk-treatment-plans.index', compact('plans', 'overdueActions', 'totalStats'));
    }

    public function show(RiskTreatmentPlan $plan): View
    {
        abort_unless(auth()->user()->can('risk.view'), 403);

        $plan->load(['risk', 'actions.owner', 'approver']);

        return view('risk-treatment-plans.show', compact('plan'));
    }
}
