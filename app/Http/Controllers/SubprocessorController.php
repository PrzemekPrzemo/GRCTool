<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Subprocessor;
use App\Models\ThirdParty;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubprocessorController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('subprocessor.view'), 403);

        $query = Subprocessor::with('thirdParty')->orderBy('name');

        if ($request->filled('tier')) {
            $query->where('tier', $request->tier);
        }
        if ($request->filled('country')) {
            $query->where('country_of_processing', 'like', "%{$request->country}%");
        }
        if ($request->boolean('public_only')) {
            $query->where('public_listing', true);
        }

        $subprocessors = $query->paginate(25)->withQueryString();

        $total    = Subprocessor::count();
        $critical = Subprocessor::where('tier', 'Critical')->count();
        $high     = Subprocessor::where('tier', 'High')->count();
        $public   = Subprocessor::where('public_listing', true)->count();

        return view('subprocessors.index', compact('subprocessors', 'total', 'critical', 'high', 'public'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('subprocessor.create'), 403);

        $thirdParties = ThirdParty::orderBy('name')->get(['id', 'name', 'code']);
        $clients      = Client::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('subprocessors.form', [
            'subprocessor' => null,
            'thirdParties' => $thirdParties,
            'clients'      => $clients,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('subprocessor.create'), 403);

        $data = $this->validateSubprocessor($request);
        $data['public_listing'] = $request->boolean('public_listing');

        $subprocessor = Subprocessor::create($data);
        AuditLogger::log('subprocessor.created', $subprocessor);

        return redirect()->route('subprocessors.show', $subprocessor)
            ->with('success', 'Subprocesor dodany.');
    }

    public function show(Subprocessor $subprocessor): View
    {
        abort_unless(auth()->user()->can('subprocessor.view'), 403);

        $subprocessor->load('thirdParty');

        $clients = Client::whereIn('id', $subprocessor->client_scopes ?? [])->get(['id', 'name']);

        return view('subprocessors.show', compact('subprocessor', 'clients'));
    }

    public function edit(Subprocessor $subprocessor): View
    {
        abort_unless(auth()->user()->can('subprocessor.update'), 403);

        $thirdParties = ThirdParty::orderBy('name')->get(['id', 'name', 'code']);
        $clients      = Client::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('subprocessors.form', [
            'subprocessor' => $subprocessor,
            'thirdParties' => $thirdParties,
            'clients'      => $clients,
        ]);
    }

    public function update(Request $request, Subprocessor $subprocessor): RedirectResponse
    {
        abort_unless(auth()->user()->can('subprocessor.update'), 403);

        $data = $this->validateSubprocessor($request);
        $data['public_listing'] = $request->boolean('public_listing');

        $subprocessor->update($data);
        AuditLogger::log('subprocessor.updated', $subprocessor);

        return redirect()->route('subprocessors.show', $subprocessor)
            ->with('success', 'Zapisano zmiany.');
    }

    public function notify(Request $request, Subprocessor $subprocessor): RedirectResponse
    {
        abort_unless(auth()->user()->can('subprocessor.update'), 403);

        $request->validate(['note' => 'nullable|string|max:1000']);

        $history = $subprocessor->notification_history ?? [];
        $history[] = [
            'date'         => now()->toISOString(),
            'notified_by'  => auth()->id(),
            'note'         => $request->note,
        ];

        $subprocessor->notification_history  = $history;
        $subprocessor->last_assessment_date  = now()->toDateString();
        $subprocessor->save();

        AuditLogger::log('subprocessor.notified', $subprocessor);

        return redirect()->route('subprocessors.show', $subprocessor)
            ->with('success', 'Notyfikacja odnotowana.');
    }

    private function validateSubprocessor(Request $request): array
    {
        return $request->validate([
            'name'                  => 'required|string|max:255',
            'service_provided'      => 'required|string|max:500',
            'third_party_id'        => 'required|exists:third_parties,id',
            'data_categories'       => 'nullable|array',
            'country_of_processing' => 'nullable|string|max:100',
            'legal_basis'           => 'nullable|string|max:255',
            'transfer_mechanism'    => 'nullable|in:SCC,adequacy,BCR',
            'dpa_url'               => 'nullable|url|max:500',
            'certifications'        => 'nullable|array',
            'client_scopes'         => 'nullable|array',
            'client_scopes.*'       => 'exists:clients,id',
            'tier'                  => 'required|in:Critical,High,Medium,Low',
            'last_assessment_date'  => 'nullable|date',
            'public_listing'        => 'boolean',
        ]);
    }
}
