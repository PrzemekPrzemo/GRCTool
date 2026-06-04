<?php

namespace App\Http\Controllers;

use App\Models\ComplianceException;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExceptionController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('exception.view'), 403);

        $query = ComplianceException::with('requester', 'approver');

        if ($type = $request->string('exception_type')->toString()) {
            $query->where('exception_type', $type);
        }
        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $exceptions = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('exceptions.index', compact('exceptions'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('exception.create'), 403);

        return view('exceptions.form', ['exception' => new ComplianceException]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('exception.create'), 403);

        $data = $this->validateException($request);
        $data['requested_by'] = auth()->id();
        $data['status'] = 'draft';

        $year = now()->format('Y');
        $count = ComplianceException::withTrashed()->whereYear('created_at', $year)->count() + 1;
        $data['code'] = sprintf('EXC-%s-%04d', $year, $count);

        $exception = ComplianceException::create($data);
        AuditLogger::log('exception.created', $exception);

        return redirect()->route('exceptions.show', $exception)->with('status', "Wyjątek {$exception->code} utworzony.");
    }

    public function show(ComplianceException $exception): View
    {
        abort_unless(auth()->user()->can('exception.view'), 403);

        $exception->load('requester', 'approver');

        return view('exceptions.show', compact('exception'));
    }

    public function edit(ComplianceException $exception): View
    {
        abort_unless(auth()->user()->can('exception.update'), 403);

        return view('exceptions.form', compact('exception'));
    }

    public function update(Request $request, ComplianceException $exception): RedirectResponse
    {
        abort_unless(auth()->user()->can('exception.update'), 403);

        $data = $this->validateException($request);
        $exception->update($data);
        AuditLogger::log('exception.updated', $exception);

        return redirect()->route('exceptions.show', $exception)->with('status', 'Zaktualizowano.');
    }

    public function submit(Request $request, ComplianceException $exception): RedirectResponse
    {
        abort_unless(auth()->user()->can('exception.update'), 403);

        $exception->update(['status' => 'pending_approval']);
        AuditLogger::log('exception.submitted', $exception);

        return back()->with('status', 'Wyjątek przesłany do zatwierdzenia.');
    }

    public function approve(Request $request, ComplianceException $exception): RedirectResponse
    {
        abort_unless(auth()->user()->can('exception.update'), 403);

        $exception->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'status' => 'approved',
        ]);
        AuditLogger::log('exception.approved', $exception);

        return back()->with('status', 'Wyjątek zatwierdzony.');
    }

    public function reject(Request $request, ComplianceException $exception): RedirectResponse
    {
        abort_unless(auth()->user()->can('exception.update'), 403);

        $data = $request->validate([
            'rejection_reason' => ['required', 'string'],
        ]);

        $exception->update([
            'status' => 'rejected',
            'rejection_reason' => $data['rejection_reason'],
        ]);
        AuditLogger::log('exception.rejected', $exception);

        return back()->with('status', 'Wyjątek odrzucony.');
    }

    private function validateException(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'exception_type' => ['required', 'in:control,risk,policy,vulnerability,other'],
            'subject_type' => ['nullable', 'string'],
            'subject_id' => ['nullable', 'integer'],
            'rationale' => ['required', 'string'],
            'compensating_controls' => ['nullable', 'string'],
            'affected_frameworks' => ['nullable', 'array'],
            'affected_frameworks.*' => ['string'],
            'expires_at' => ['nullable', 'date'],
        ]);
    }
}
