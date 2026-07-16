<?php

namespace App\Http\Controllers;

use App\Models\RiskAcceptance;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiskAcceptanceController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('risk.view'), 403);

        $query = RiskAcceptance::with('risk', 'proposer', 'approver');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $acceptances = $query->orderByDesc('proposed_at')->paginate(25)->withQueryString();

        $stats = [
            'pending' => RiskAcceptance::where('status', 'Pending')->count(),
            'approved' => RiskAcceptance::where('status', 'Approved')->count(),
            'expiring_soon' => RiskAcceptance::where('status', 'Approved')
                ->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
                ->count(),
            'expired' => RiskAcceptance::where('status', 'Expired')->count(),
        ];

        return view('risk-acceptances.index', compact('acceptances', 'stats'));
    }

    public function show(RiskAcceptance $acceptance): View
    {
        $acceptance->load('risk', 'proposer', 'approver', 'evidence');

        // Automatyczne oznaczanie jako Expired jeśli minęła data ważności
        if (
            $acceptance->status === 'Approved'
            && $acceptance->expiry_date !== null
            && $acceptance->expiry_date->isPast()
        ) {
            $acceptance->update(['status' => 'Expired']);
            $acceptance->status = 'Expired';
        }

        return view('risk-acceptances.show', compact('acceptance'));
    }

    public function approve(Request $request, RiskAcceptance $acceptance): RedirectResponse
    {
        abort_unless(auth()->user()->can('risk.update'), 403);

        $data = $request->validate([
            'expiry_date' => ['required', 'date', 'after:today'],
            'rationale_comment' => ['nullable', 'string', 'max:500'],
        ]);

        $acceptance->update([
            'status' => 'Approved',
            'accepted_by' => auth()->id(),
            'accepted_at' => now(),
            'expiry_date' => $data['expiry_date'],
        ]);

        AuditLogger::log('risk.acceptance_approved', $acceptance->risk);

        return redirect()
            ->route('risk-acceptances.show', $acceptance)
            ->with('status', 'Akceptacja ryzyka zatwierdzona.');
    }

    public function reject(Request $request, RiskAcceptance $acceptance): RedirectResponse
    {
        abort_unless(auth()->user()->can('risk.update'), 403);

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $acceptance->update([
            'status' => 'Rejected',
        ]);

        AuditLogger::log('risk.acceptance_rejected', $acceptance->risk);

        return redirect()
            ->route('risk-acceptances.show', $acceptance)
            ->with('status', 'Akceptacja ryzyka odrzucona.');
    }

    public function revoke(Request $request, RiskAcceptance $acceptance): RedirectResponse
    {
        abort_unless(auth()->user()->can('risk.update'), 403);

        $data = $request->validate([
            'revoke_reason' => ['required', 'string', 'max:1000'],
        ]);

        $acceptance->update([
            'status' => 'Revoked',
            'revoked_at' => now(),
            'revoked_by' => auth()->id(),
            'revoke_reason' => $data['revoke_reason'],
        ]);

        AuditLogger::log('risk.acceptance_revoked', $acceptance->risk);

        return redirect()
            ->route('risk-acceptances.show', $acceptance)
            ->with('status', 'Akceptacja ryzyka cofnięta.');
    }
}
