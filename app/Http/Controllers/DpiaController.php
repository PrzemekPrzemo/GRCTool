<?php

namespace App\Http\Controllers;

use App\Models\Dpia;
use App\Models\ProcessingActivity;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DpiaController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('dpia.view'), 403);

        $query = Dpia::query()
            ->with(['processingActivity', 'conductedBy'])
            ->orderByDesc('assessment_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('risk_level')) {
            $query->where('overall_risk_level', $request->risk_level);
        }

        $dpias = $query->paginate(25)->withQueryString();

        return view('dpia.index', compact('dpias'));
    }

    public function create(Request $request): View
    {
        abort_unless(auth()->user()->can('dpia.create'), 403);

        $preselectedActivity = $request->filled('activity')
            ? ProcessingActivity::find($request->activity)
            : null;

        return view('dpia.form', [
            'dpia' => null,
            'users' => User::orderBy('name')->get(),
            'activities' => ProcessingActivity::where('status', 'active')->orderBy('name')->get(),
            'preselectedActivity' => $preselectedActivity,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('dpia.create'), 403);

        $data = $this->validateDpia($request);
        $data['code'] = $this->generateCode();

        $dpia = Dpia::create($data);
        AuditLogger::log('dpia.created', $dpia);

        return redirect()->route('dpias.show', $dpia)->with('success', 'DPIA zarejestrowana.');
    }

    public function show(Dpia $dpia): View
    {
        abort_unless(auth()->user()->can('dpia.view'), 403);

        $dpia->load(['processingActivity', 'conductedBy', 'reviewer']);

        return view('dpia.show', compact('dpia'));
    }

    public function edit(Dpia $dpia): View
    {
        abort_unless(auth()->user()->can('dpia.update'), 403);

        return view('dpia.form', [
            'dpia' => $dpia,
            'users' => User::orderBy('name')->get(),
            'activities' => ProcessingActivity::where('status', 'active')->orderBy('name')->get(),
            'preselectedActivity' => null,
        ]);
    }

    public function update(Request $request, Dpia $dpia): RedirectResponse
    {
        abort_unless(auth()->user()->can('dpia.update'), 403);

        $data = $this->validateDpia($request);
        $dpia->update($data);
        AuditLogger::log('dpia.updated', $dpia);

        return redirect()->route('dpias.show', $dpia)->with('success', 'Zapisano zmiany.');
    }

    public function approve(Dpia $dpia): RedirectResponse
    {
        abort_unless(auth()->user()->can('dpia.update'), 403);

        $dpia->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        AuditLogger::log('dpia.approved', $dpia);

        return back()->with('success', 'DPIA zatwierdzona.');
    }

    private function validateDpia(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'processing_activity_id' => 'nullable|exists:processing_activities,id',
            'conducted_by' => 'nullable|exists:users,id',
            'assessment_date' => 'nullable|date',
            'necessity_assessment' => 'nullable|string',
            'proportionality_assessment' => 'nullable|string',
            'identified_risks' => 'nullable|array',
            'identified_risks.*.description' => 'required|string',
            'identified_risks.*.likelihood' => 'required|in:low,medium,high',
            'identified_risks.*.impact' => 'required|in:low,medium,high',
            'overall_risk_level' => 'nullable|in:low,medium,high,very_high',
            'mitigation_measures' => 'nullable|array',
            'dpo_consulted' => 'boolean',
            'dpo_opinion' => 'nullable|string',
            'dpo_consulted_at' => 'nullable|date',
            'authority_consultation_required' => 'boolean',
            'authority_consulted_at' => 'nullable|date',
            'authority_response' => 'nullable|string',
            'status' => 'required|in:draft,in_review,approved,rejected',
            'review_notes' => 'nullable|string',
        ]);
    }

    private function generateCode(): string
    {
        $count = Dpia::withTrashed()->count() + 1;

        return sprintf('DPIA-%s-%04d', now()->format('Y'), $count);
    }
}
