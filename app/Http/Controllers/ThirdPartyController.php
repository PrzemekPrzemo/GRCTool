<?php

namespace App\Http\Controllers;

use App\Models\ThirdParty;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThirdPartyController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('third_party.view'), 403);

        $query = ThirdParty::query()->orderBy('name');

        if ($request->filled('tier')) {
            $query->where('tier', $request->tier);
        }
        if ($request->boolean('inactive')) {
            $query->where('is_active', false);
        } else {
            $query->where('is_active', true);
        }
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request): void {
                $q->where('name', 'like', "%{$request->q}%")
                  ->orWhere('code', 'like', "%{$request->q}%")
                  ->orWhere('service_provided', 'like', "%{$request->q}%");
            });
        }

        $parties = $query->paginate(25)->withQueryString();

        return view('third_party.index', compact('parties'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('third_party.create'), 403);

        return view('third_party.form', ['party' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('third_party.create'), 403);

        $data = $this->validateParty($request);
        $data['code'] = $this->generateCode($data['name']);

        $party = ThirdParty::create($data);
        AuditLogger::log('third_party.created', $party);

        return redirect()->route('third-parties.show', $party)->with('success', 'Strona trzecia dodana.');
    }

    public function show(ThirdParty $thirdParty): View
    {
        abort_unless(auth()->user()->can('third_party.view'), 403);

        $thirdParty->load('processingActivities');

        return view('third_party.show', ['party' => $thirdParty]);
    }

    public function edit(ThirdParty $thirdParty): View
    {
        abort_unless(auth()->user()->can('third_party.update'), 403);

        return view('third_party.form', ['party' => $thirdParty]);
    }

    public function update(Request $request, ThirdParty $thirdParty): RedirectResponse
    {
        abort_unless(auth()->user()->can('third_party.update'), 403);

        $data = $this->validateParty($request);
        $thirdParty->update($data);
        AuditLogger::log('third_party.updated', $thirdParty);

        return redirect()->route('third-parties.show', $thirdParty)->with('success', 'Zapisano zmiany.');
    }

    public function destroy(ThirdParty $thirdParty): RedirectResponse
    {
        abort_unless(auth()->user()->can('third_party.delete'), 403);

        AuditLogger::log('third_party.deleted', $thirdParty);
        $thirdParty->delete();

        return redirect()->route('third-parties.index')->with('success', 'Strona trzecia usunięta.');
    }

    private function validateParty(Request $request): array
    {
        return $request->validate([
            'name'                  => 'required|string|max:255',
            'service_provided'      => 'nullable|string|max:255',
            'data_categories'       => 'nullable|array',
            'country_of_processing' => 'nullable|string|max:64',
            'legal_basis'           => 'nullable|string|max:128',
            'transfer_mechanism'    => 'nullable|string|max:32',
            'dpa_url'               => 'nullable|url|max:1024',
            'certifications'        => 'nullable|array',
            'tier'                  => 'required|in:Critical,High,Medium,Low',
            'last_assessment_date'  => 'nullable|date',
            'next_assessment_due'   => 'nullable|date',
            'security_rating'       => 'nullable|integer|min:0|max:100',
            'is_active'             => 'boolean',
        ]);
    }

    private function generateCode(string $name): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 4));
        $count = ThirdParty::withTrashed()->count() + 1;

        return sprintf('TP-%s-%04d', $prefix ?: 'ORG', $count);
    }
}
