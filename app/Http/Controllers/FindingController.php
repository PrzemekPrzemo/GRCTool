<?php

namespace App\Http\Controllers;

use App\Models\Finding;
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
