<?php

namespace App\Http\Controllers;

use App\Models\ProcessingActivity;
use App\Models\ThirdParty;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProcessingActivityController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('rcp.view'), 403);

        $query = ProcessingActivity::query()
            ->with(['controller', 'thirdParties'])
            ->orderByDesc('created_at');

        if ($request->filled('legal_basis')) {
            $query->where('legal_basis', $request->legal_basis);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request): void {
                $q->where('name', 'like', "%{$request->q}%")
                    ->orWhere('code', 'like', "%{$request->q}%");
            });
        }

        $activities = $query->paginate(25)->withQueryString();

        return view('rcp.index', [
            'activities' => $activities,
            'legalBases' => ProcessingActivity::LEGAL_BASES,
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('rcp.create'), 403);

        return view('rcp.form', [
            'activity' => null,
            'users' => User::orderBy('name')->get(),
            'thirdParties' => ThirdParty::where('is_active', true)->orderBy('name')->get(),
            'legalBases' => ProcessingActivity::LEGAL_BASES,
            'dataCategories' => ProcessingActivity::DATA_CATEGORIES,
            'specialCategories' => ProcessingActivity::SPECIAL_CATEGORIES,
            'dataSubjects' => ProcessingActivity::DATA_SUBJECTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('rcp.create'), 403);

        $data = $this->validateActivity($request);
        $data['code'] = $this->generateCode();

        $activity = ProcessingActivity::create($data);
        $this->syncThirdParties($activity, $request);

        AuditLogger::log('rcp.created', $activity);

        return redirect()->route('rcp.show', $activity)->with('success', 'Czynność przetwarzania zarejestrowana.');
    }

    public function show(ProcessingActivity $rcp): View
    {
        abort_unless(auth()->user()->can('rcp.view'), 403);

        $rcp->load(['controller', 'processor', 'thirdParties', 'dpias.conductedBy']);

        return view('rcp.show', compact('rcp'));
    }

    public function edit(ProcessingActivity $rcp): View
    {
        abort_unless(auth()->user()->can('rcp.update'), 403);

        return view('rcp.form', [
            'activity' => $rcp->load('thirdParties'),
            'users' => User::orderBy('name')->get(),
            'thirdParties' => ThirdParty::where('is_active', true)->orderBy('name')->get(),
            'legalBases' => ProcessingActivity::LEGAL_BASES,
            'dataCategories' => ProcessingActivity::DATA_CATEGORIES,
            'specialCategories' => ProcessingActivity::SPECIAL_CATEGORIES,
            'dataSubjects' => ProcessingActivity::DATA_SUBJECTS,
        ]);
    }

    public function update(Request $request, ProcessingActivity $rcp): RedirectResponse
    {
        abort_unless(auth()->user()->can('rcp.update'), 403);

        $data = $this->validateActivity($request);
        $rcp->update($data);
        $this->syncThirdParties($rcp, $request);

        AuditLogger::log('rcp.updated', $rcp);

        return redirect()->route('rcp.show', $rcp)->with('success', 'Zapisano zmiany.');
    }

    public function destroy(ProcessingActivity $rcp): RedirectResponse
    {
        abort_unless(auth()->user()->can('rcp.delete'), 403);

        AuditLogger::log('rcp.deleted', $rcp);
        $rcp->delete();

        return redirect()->route('rcp.index')->with('success', 'Czynność przetwarzania usunięta.');
    }

    private function validateActivity(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'purpose' => 'nullable|string|max:500',
            'legal_basis' => 'nullable|string|max:64',
            'legal_basis_detail' => 'nullable|string',
            'data_categories' => 'nullable|array',
            'special_categories' => 'nullable|array',
            'data_subjects' => 'nullable|array',
            'retention_period' => 'nullable|string|max:128',
            'retention_basis' => 'nullable|string',
            'controller_id' => 'nullable|exists:users,id',
            'processor_id' => 'nullable|exists:users,id',
            'system_name' => 'nullable|string|max:255',
            'security_measures' => 'nullable|array',
            'cross_border_transfer' => 'boolean',
            'transfer_countries' => 'nullable|array',
            'transfer_mechanism' => 'nullable|string|max:64',
            'dpia_required' => 'boolean',
            'status' => 'required|in:active,under_review,archived',
            'notes' => 'nullable|string',
        ]);
    }

    private function syncThirdParties(ProcessingActivity $activity, Request $request): void
    {
        $ids = $request->input('third_party_ids', []);
        $roles = $request->input('third_party_roles', []);
        $sync = [];
        foreach ($ids as $id) {
            $sync[$id] = ['role' => $roles[$id] ?? 'processor'];
        }
        $activity->thirdParties()->sync($sync);
    }

    private function generateCode(): string
    {
        $count = ProcessingActivity::withTrashed()->count() + 1;

        return sprintf('RCP-%s-%04d', now()->format('Y'), $count);
    }
}
