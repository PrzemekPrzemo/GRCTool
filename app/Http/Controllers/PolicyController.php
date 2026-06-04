<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use App\Models\PolicyAttestation;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PolicyController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('policy.view'), 403);

        $query = Policy::query()
            ->with('owner')
            ->orderBy('title');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $policies = $query->paginate(25)->withQueryString();
        $categories = Policy::distinct()->pluck('category')->filter()->sort()->values();

        return view('policy.index', compact('policies', 'categories'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('policy.create'), 403);

        return view('policy.form', [
            'policy' => null,
            'users'  => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('policy.create'), 403);

        $data = $this->validatePolicy($request);
        $data['code'] = $this->generateCode();

        $policy = Policy::create($data);
        AuditLogger::log('policy.created', $policy);

        return redirect()->route('policies.show', $policy)->with('success', 'Polityka dodana.');
    }

    public function show(Policy $policy): View
    {
        abort_unless(auth()->user()->can('policy.view'), 403);

        $policy->load(['owner', 'approver', 'attestations.user']);
        $userAttestation = $policy->attestations()
            ->where('user_id', auth()->id())
            ->where('policy_version', $policy->current_version)
            ->first();

        return view('policy.show', compact('policy', 'userAttestation'));
    }

    public function edit(Policy $policy): View
    {
        abort_unless(auth()->user()->can('policy.update'), 403);

        return view('policy.form', [
            'policy' => $policy,
            'users'  => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Policy $policy): RedirectResponse
    {
        abort_unless(auth()->user()->can('policy.update'), 403);

        $data = $this->validatePolicy($request);
        $policy->update($data);
        AuditLogger::log('policy.updated', $policy);

        return redirect()->route('policies.show', $policy)->with('success', 'Zapisano zmiany.');
    }

    public function approve(Policy $policy): RedirectResponse
    {
        abort_unless(auth()->user()->can('policy.update'), 403);

        $policy->update([
            'status'      => 'Active',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        AuditLogger::log('policy.approved', $policy);

        return back()->with('success', 'Polityka zatwierdzona i aktywna.');
    }

    public function attest(Policy $policy): RedirectResponse
    {
        abort_unless(auth()->user()->can('policy.view'), 403);

        PolicyAttestation::updateOrCreate(
            [
                'policy_id'      => $policy->id,
                'user_id'        => auth()->id(),
                'policy_version' => $policy->current_version,
            ],
            [
                'attested_at' => now(),
                'ip_address'  => request()->ip(),
            ]
        );

        AuditLogger::log('policy.attested', $policy);

        return back()->with('success', 'Zatwierdzono zapoznanie się z polityką.');
    }

    private function validatePolicy(Request $request): array
    {
        return $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',
            'category'            => 'nullable|string|max:64',
            'current_version'     => 'required|string|max:16',
            'effective_from'      => 'nullable|date',
            'next_review_due'     => 'nullable|date',
            'owner_id'            => 'nullable|exists:users,id',
            'status'              => 'required|in:Draft,Approved,Active,Retired',
            'framework_mappings'  => 'nullable|array',
            'attestation_required' => 'boolean',
        ]);
    }

    private function generateCode(): string
    {
        $count = Policy::withTrashed()->count() + 1;

        return sprintf('POL-%s-%04d', now()->format('Y'), $count);
    }
}
