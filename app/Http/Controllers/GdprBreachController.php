<?php

namespace App\Http\Controllers;

use App\Models\GdprBreach;
use App\Models\Incident;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GdprBreachController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('gdpr_breach.view'), 403);

        $query = GdprBreach::query()
            ->with('responsibleUser')
            ->orderByDesc('discovered_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }
        if ($request->filled('notification')) {
            $query->where('notification_required', true);
        }

        $breaches = $query->paginate(25)->withQueryString();

        return view('gdpr_breach.index', compact('breaches'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('gdpr_breach.create'), 403);

        return view('gdpr_breach.form', [
            'breach' => null,
            'users' => User::orderBy('name')->get(),
            'incidents' => Incident::whereIn('status', ['New', 'Investigating', 'Containment', 'Eradication'])
                ->orderByDesc('created_at')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('gdpr_breach.create'), 403);

        $data = $this->validateBreach($request);
        $data['code'] = $this->generateCode();

        if (! empty($data['discovered_at']) && ! empty($data['notification_required'])) {
            $data['uodo_notification_deadline'] = Carbon::parse($data['discovered_at'])->addHours(72);
        }

        $breach = GdprBreach::create($data);
        AuditLogger::log('gdpr_breach.created', $breach);

        return redirect()->route('gdpr-breaches.show', $breach)->with('success', 'Naruszenie danych zarejestrowane.');
    }

    public function show(GdprBreach $gdprBreach): View
    {
        abort_unless(auth()->user()->can('gdpr_breach.view'), 403);

        $gdprBreach->load(['responsibleUser', 'incident']);

        return view('gdpr_breach.show', ['breach' => $gdprBreach]);
    }

    public function edit(GdprBreach $gdprBreach): View
    {
        abort_unless(auth()->user()->can('gdpr_breach.update'), 403);

        return view('gdpr_breach.form', [
            'breach' => $gdprBreach,
            'users' => User::orderBy('name')->get(),
            'incidents' => Incident::orderByDesc('created_at')->get(),
        ]);
    }

    public function update(Request $request, GdprBreach $gdprBreach): RedirectResponse
    {
        abort_unless(auth()->user()->can('gdpr_breach.update'), 403);

        $data = $this->validateBreach($request);

        if (! empty($data['discovered_at']) && ! empty($data['notification_required']) && ! $gdprBreach->uodo_notification_deadline) {
            $data['uodo_notification_deadline'] = Carbon::parse($data['discovered_at'])->addHours(72);
        }

        $gdprBreach->update($data);
        AuditLogger::log('gdpr_breach.updated', $gdprBreach);

        return redirect()->route('gdpr-breaches.show', $gdprBreach)->with('success', 'Zapisano zmiany.');
    }

    public function destroy(GdprBreach $gdprBreach): RedirectResponse
    {
        abort_unless(auth()->user()->can('gdpr_breach.delete'), 403);

        AuditLogger::log('gdpr_breach.deleted', $gdprBreach);
        $gdprBreach->delete();

        return redirect()->route('gdpr-breaches.index')->with('success', 'Naruszenie usunięte.');
    }

    public function notifyUodo(Request $request, GdprBreach $gdprBreach): RedirectResponse
    {
        abort_unless(auth()->user()->can('gdpr_breach.update'), 403);

        $request->validate([
            'uodo_reference_number' => 'nullable|string|max:64',
        ]);

        $gdprBreach->update([
            'uodo_notified_at' => now(),
            'uodo_reference_number' => $request->uodo_reference_number,
            'status' => 'reported',
        ]);

        AuditLogger::log('gdpr_breach.uodo_notified', $gdprBreach);

        return back()->with('success', 'Zgłoszenie do UODO zarejestrowane.');
    }

    private function validateBreach(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'breach_type' => 'nullable|in:confidentiality,integrity,availability',
            'occurred_at' => 'nullable|date',
            'discovered_at' => 'nullable|date',
            'contained_at' => 'nullable|date',
            'incident_id' => 'nullable|exists:incidents,id',
            'data_categories_affected' => 'nullable|array',
            'special_categories_affected' => 'nullable|array',
            'data_subjects_count' => 'nullable|integer|min:0',
            'data_subjects_types' => 'nullable|array',
            'risk_level' => 'nullable|in:low,medium,high,very_high',
            'risk_description' => 'nullable|string',
            'notification_required' => 'boolean',
            'data_subject_notification_required' => 'boolean',
            'uodo_reference_number' => 'nullable|string|max:64',
            'uodo_notified_at' => 'nullable|date',
            'data_subjects_notified' => 'boolean',
            'data_subjects_notified_at' => 'nullable|date',
            'remediation_actions' => 'nullable|string',
            'preventive_measures' => 'nullable|string',
            'responsible_user_id' => 'nullable|exists:users,id',
            'status' => 'required|in:open,contained,closed,reported',
            'notes' => 'nullable|string',
        ]);
    }

    private function generateCode(): string
    {
        $count = GdprBreach::withTrashed()->count() + 1;

        return sprintf('BREACH-%s-%04d', now()->format('Y'), $count);
    }
}
