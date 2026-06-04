<?php

namespace App\Http\Controllers;

use App\Models\DsarRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DsarRequestController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('dsar.view'), 403);

        $query = DsarRequest::query()
            ->with('assignedUser')
            ->orderByDesc('received_at');

        if ($request->filled('type')) {
            $query->where('request_type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('overdue')) {
            $query->where('deadline_at', '<', now())
                ->whereNotIn('status', ['completed', 'rejected', 'withdrawn']);
        }

        $requests = $query->paginate(25)->withQueryString();

        return view('dsar.index', [
            'requests'     => $requests,
            'requestTypes' => DsarRequest::REQUEST_TYPES,
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('dsar.create'), 403);

        return view('dsar.form', [
            'dsar'         => null,
            'users'        => User::orderBy('name')->get(),
            'requestTypes' => DsarRequest::REQUEST_TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('dsar.create'), 403);

        $data = $this->validateDsar($request);
        $data['code'] = $this->generateCode();

        if (!empty($data['received_at'])) {
            $data['deadline_at'] = Carbon::parse($data['received_at'])->addDays(30);
        }

        $dsar = DsarRequest::create($data);
        AuditLogger::log('dsar.created', $dsar);

        return redirect()->route('dsar.show', $dsar)->with('success', 'Wniosek DSAR zarejestrowany.');
    }

    public function show(DsarRequest $dsar): View
    {
        abort_unless(auth()->user()->can('dsar.view'), 403);

        $dsar->load('assignedUser');

        return view('dsar.show', compact('dsar'));
    }

    public function edit(DsarRequest $dsar): View
    {
        abort_unless(auth()->user()->can('dsar.update'), 403);

        return view('dsar.form', [
            'dsar'         => $dsar,
            'users'        => User::orderBy('name')->get(),
            'requestTypes' => DsarRequest::REQUEST_TYPES,
        ]);
    }

    public function update(Request $request, DsarRequest $dsar): RedirectResponse
    {
        abort_unless(auth()->user()->can('dsar.update'), 403);

        $data = $this->validateDsar($request);
        $dsar->update($data);
        AuditLogger::log('dsar.updated', $dsar);

        return redirect()->route('dsar.show', $dsar)->with('success', 'Zapisano zmiany.');
    }

    public function complete(Request $request, DsarRequest $dsar): RedirectResponse
    {
        abort_unless(auth()->user()->can('dsar.update'), 403);

        $request->validate([
            'outcome'       => 'required|in:fulfilled,partially_fulfilled,rejected_no_data,rejected_identity,rejected_legal',
            'outcome_notes' => 'nullable|string',
        ]);

        $dsar->update([
            'status'       => 'completed',
            'outcome'      => $request->outcome,
            'outcome_notes' => $request->outcome_notes,
            'completed_at' => now(),
        ]);

        AuditLogger::log('dsar.completed', $dsar);

        return back()->with('success', 'Wniosek oznaczony jako zakończony.');
    }

    public function extend(Request $request, DsarRequest $dsar): RedirectResponse
    {
        abort_unless(auth()->user()->can('dsar.update'), 403);

        $request->validate(['extension_reason' => 'required|string|max:500']);

        $dsar->update([
            'deadline_extended'   => true,
            'extended_deadline_at' => Carbon::parse($dsar->received_at)->addDays(90),
            'extension_reason'    => $request->extension_reason,
        ]);

        AuditLogger::log('dsar.extended', $dsar);

        return back()->with('success', 'Termin przedłużony do 90 dni.');
    }

    private function validateDsar(Request $request): array
    {
        return $request->validate([
            'request_type'               => 'required|in:access,rectification,erasure,restriction,portability,objection,withdraw_consent',
            'requester_name'             => 'required|string|max:255',
            'requester_email'            => 'nullable|email|max:255',
            'requester_details'          => 'nullable|string',
            'request_description'        => 'required|string',
            'received_at'                => 'required|date',
            'assigned_to'                => 'nullable|exists:users,id',
            'status'                     => 'required|in:pending,in_progress,on_hold,completed,rejected,withdrawn',
            'handling_notes'             => 'nullable|string',
            'identity_verified'          => 'boolean',
            'identity_verification_notes' => 'nullable|string',
        ]);
    }

    private function generateCode(): string
    {
        $count = DsarRequest::withTrashed()->count() + 1;

        return sprintf('DSAR-%s-%04d', now()->format('Y'), $count);
    }
}
