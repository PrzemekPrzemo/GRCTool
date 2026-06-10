<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('client.view'), 403);

        $query = Client::query()->withCount('projects')->orderBy('name');

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
                  ->orWhere('industry', 'like', "%{$request->q}%");
            });
        }

        $clients = $query->paginate(25)->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('client.create'), 403);

        return view('clients.form', ['client' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('client.create'), 403);

        $data = $this->validateClient($request);
        $client = Client::create($data);
        AuditLogger::log('client.created', $client);

        return redirect()->route('clients.show', $client)->with('success', 'Klient został dodany.');
    }

    public function show(Client $client): View
    {
        abort_unless(auth()->user()->can('client.view'), 403);

        $client->load('projects');

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client): View
    {
        abort_unless(auth()->user()->can('client.update'), 403);

        return view('clients.form', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        abort_unless(auth()->user()->can('client.update'), 403);

        $data = $this->validateClient($request, $client->id);
        $client->update($data);
        AuditLogger::log('client.updated', $client);

        return redirect()->route('clients.show', $client)->with('success', 'Zapisano zmiany.');
    }

    private function validateClient(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = 'unique:clients,code' . ($ignoreId ? ",{$ignoreId}" : '');

        $data = $request->validate([
            'code'                               => ['required', 'string', 'max:32', $uniqueRule],
            'name'                               => ['required', 'string', 'max:255'],
            'industry'                           => ['nullable', 'string', 'max:100'],
            'tier'                               => ['required', 'in:Enterprise,Mid-market,SMB'],
            'applicable_frameworks'              => ['nullable', 'array'],
            'contractual_security_requirements'  => ['nullable', 'string'],
            'subprocessor_notification_required' => ['boolean'],
            'notification_lead_time_days'        => ['nullable', 'integer', 'min:1'],
            'nda_signed_at'                      => ['nullable', 'date'],
            'is_active'                          => ['boolean'],
        ]);

        // Convert newline-delimited textarea to array for JSON storage
        if (isset($data['contractual_security_requirements'])) {
            $lines = array_filter(
                array_map('trim', explode("\n", $data['contractual_security_requirements'])),
                fn ($l) => $l !== ''
            );
            $data['contractual_security_requirements'] = array_values($lines);
        }

        return $data;
    }
}
