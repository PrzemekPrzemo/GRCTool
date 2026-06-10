<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('incident.view'), 403);

        $q = Incident::query()->with('owner');

        if ($search = $request->string('q')->trim()->toString()) {
            $q->where(fn ($s) => $s->where('title', 'like', "%$search%")->orWhere('code', 'like', "%$search%"));
        }
        if ($sev = $request->string('severity')->toString()) {
            $q->where('severity', $sev);
        }
        if ($status = $request->string('status')->toString()) {
            $q->where('status', $status);
        }
        if ($request->boolean('breach')) {
            $q->where('is_breach', true);
        }

        $incidents = $q->orderByDesc('occurred_at')->orderByDesc('id')->paginate(25)->withQueryString();

        return view('incidents.index', compact('incidents'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('incident.create'), 403);

        return view('incidents.form', $this->formData(new Incident));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('incident.create'), 403);

        $data = $this->validateIncident($request);
        $data['code'] = sprintf('INC-%s-%05d', now()->format('Y'), Incident::withTrashed()->count() + 1);

        $incident = Incident::create($data);
        AuditLogger::log('incident_created', $incident);
        \App\Services\SlackNotifier::incidentCreated($incident);

        return redirect()->route('incidents.show', $incident)
            ->with('status', "Incydent {$incident->code} zarejestrowany.");
    }

    public function show(Incident $incident): View
    {
        abort_unless(auth()->user()->can('incident.view'), 403);
        $incident->load('owner');

        return view('incidents.show', compact('incident'));
    }

    public function edit(Incident $incident): View
    {
        abort_unless(auth()->user()->can('incident.update'), 403);

        return view('incidents.form', $this->formData($incident));
    }

    public function update(Request $request, Incident $incident): RedirectResponse
    {
        abort_unless(auth()->user()->can('incident.update'), 403);

        $data = $this->validateIncident($request);
        $original = $incident->getOriginal();
        $incident->update($data);

        $diff = [];
        foreach ($incident->getChanges() as $k => $v) {
            if ($k === 'updated_at') {
                continue;
            }
            $diff[$k] = ['old' => $original[$k] ?? null, 'new' => $v];
        }
        if ($diff) {
            AuditLogger::log('incident_updated', $incident, ['changes' => $diff]);
        }

        return redirect()->route('incidents.show', $incident)
            ->with('status', "Incydent {$incident->code} zaktualizowany.");
    }

    public function destroy(Incident $incident): RedirectResponse
    {
        abort_unless(auth()->user()->can('incident.delete'), 403);
        $incident->delete();
        AuditLogger::log('incident_deleted', $incident);

        return redirect()->route('incidents.index')
            ->with('status', "Incydent {$incident->code} usunięty.");
    }

    /**
     * Advance the incident through its response workflow.
     */
    public function updateStatus(Request $request, Incident $incident): RedirectResponse
    {
        abort_unless(auth()->user()->can('incident.update'), 403);

        $data = $request->validate([
            'status' => ['required', 'in:New,Investigating,Containment,Eradication,Recovery,Closed'],
        ]);

        $timestampMap = [
            'Investigating' => 'acknowledged_at',
            'Containment'   => 'contained_at',
            'Closed'        => 'resolved_at',
            'Recovery'      => 'resolved_at',
        ];

        $updates = ['status' => $data['status']];
        if (isset($timestampMap[$data['status']])) {
            $field = $timestampMap[$data['status']];
            if (! $incident->$field) {
                $updates[$field] = now();
            }
        }

        $incident->update($updates);
        AuditLogger::log('incident_status_changed', $incident, ['status' => $data['status']]);

        return back()->with('status', "Status zmieniony na: {$data['status']}.");
    }

    /**
     * Toggle is_breach flag (RODO/NIS2/DORA notification requirement).
     */
    public function toggleBreach(Incident $incident): RedirectResponse
    {
        abort_unless(auth()->user()->can('incident.update'), 403);

        $incident->update(['is_breach' => ! $incident->is_breach]);
        $label = $incident->is_breach ? 'oznaczony jako BREACH' : 'odznaczony jako BREACH';
        AuditLogger::log('incident_breach_toggled', $incident, ['is_breach' => $incident->is_breach]);
        if ($incident->is_breach) {
            \App\Services\SlackNotifier::incidentBreachFlagged($incident);
        }

        return back()->with('status', "Incydent $label.");
    }

    private function formData(Incident $incident): array
    {
        return [
            'incident'   => $incident,
            'users'      => User::orderBy('name')->get(['id', 'name']),
            'severities' => Incident::SEVERITIES,
            'statuses'   => Incident::STATUSES,
            'sources'    => Incident::SOURCES,
        ];
    }

    private function validateIncident(Request $request): array
    {
        $data = $request->validate([
            'title'                      => ['required', 'string', 'max:255'],
            'description'                => ['nullable', 'string'],
            'severity'                   => ['required', 'in:Critical,High,Medium,Low'],
            'status'                     => ['required', 'in:New,Investigating,Containment,Eradication,Recovery,Closed'],
            'source'                     => ['nullable', 'string', 'max:32'],
            'owner_id'                   => ['nullable', 'exists:users,id'],
            'occurred_at'                => ['nullable', 'date'],
            'detected_at'                => ['nullable', 'date'],
            'acknowledged_at'            => ['nullable', 'date'],
            'contained_at'               => ['nullable', 'date'],
            'resolved_at'                => ['nullable', 'date'],
            'is_breach'                  => ['boolean'],
            'estimated_cost_eur'         => ['nullable', 'numeric', 'min:0'],
            'post_mortem'                => ['nullable', 'string'],
            // ENISA fields
            'enisa_users_affected_band'  => ['nullable', 'in:lt100,lt1k,lt10k,lt100k,ge100k'],
            'enisa_service_impact'       => ['nullable', 'in:none,minimal,partial,significant,full'],
            'enisa_geographic_spread'    => ['nullable', 'in:local,regional,national,cross_border'],
            'enisa_duration_hours'       => ['nullable', 'numeric', 'min:0'],
            'enisa_economic_impact'      => ['nullable', 'in:negligible,low,moderate,significant,severe'],
            // Array fields as comma-separated strings from form
            'affected_clients_raw'       => ['nullable', 'string'],
            'affected_assets_raw'        => ['nullable', 'string'],
            'linked_risks_raw'           => ['nullable', 'string'],
            'linked_controls_raw'        => ['nullable', 'string'],
        ]);

        // Convert comma-separated → arrays
        foreach (['affected_clients', 'affected_assets', 'linked_risks', 'linked_controls'] as $field) {
            $raw = $data["{$field}_raw"] ?? '';
            $data[$field] = array_values(array_filter(array_map('trim', explode(',', $raw))));
            unset($data["{$field}_raw"]);
        }

        $data['is_breach'] = $request->boolean('is_breach');

        return $data;
    }
}
