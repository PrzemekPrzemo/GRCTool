<?php

namespace App\Http\Controllers;

use App\Models\ComplianceGap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplianceGapController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('compliance.view'), 403);

        $query = ComplianceGap::query()
            ->orderByRaw("case severity when 'critical' then 0 when 'high' then 1 else 2 end")
            ->orderBy('target_date');

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $gaps = $query->get();

        return view('compliance.gaps', compact('gaps'));
    }

    public function updateStatus(Request $request, ComplianceGap $gap): RedirectResponse
    {
        abort_unless(auth()->user()->can('compliance.update'), 403);

        $data = $request->validate([
            'status' => 'required|in:open,in_progress,closed',
        ]);

        $gap->update($data);

        return back()->with('success', 'Status luki zaktualizowany.');
    }
}
