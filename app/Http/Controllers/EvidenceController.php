<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\EvidenceObject;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class EvidenceController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('evidence.view'), 403);

        $q = EvidenceObject::query()->with('uploader');

        if ($classification = $request->string('classification')->toString()) {
            $q->where('classification', $classification);
        }

        if ($expiryStatus = $request->string('expiry_status')->toString()) {
            match ($expiryStatus) {
                'valid'         => $q->where(function ($sub) {
                    $sub->whereNull('valid_until')
                        ->orWhere('valid_until', '>', now()->addDays(30));
                }),
                'expiring_soon' => $q->expiringSoon(30),
                'expired'       => $q->expired(),
                default         => null,
            };
        }

        if ($search = $request->string('search')->toString()) {
            $q->where('title', 'like', '%' . $search . '%');
        }

        $evidence = $q->orderByDesc('created_at')->paginate(25)->withQueryString();

        $stats = [
            'total'         => EvidenceObject::count(),
            'expiring_soon' => EvidenceObject::expiringSoon(30)->count(),
            'expired'       => EvidenceObject::expired()->count(),
        ];

        return view('evidence.index', compact('evidence', 'stats'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('evidence.create'), 403);

        $clients = Client::where('is_active', true)->orderBy('name')->get();

        return view('evidence.form', [
            'evidence' => new EvidenceObject,
            'clients'  => $clients,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('evidence.create'), 403);

        $this->mergeTagsFromRaw($request);
        $data = $this->validateEvidence($request);
        $data['uuid']        = (string) Str::uuid();
        $data['uploaded_by'] = auth()->id();

        $evidence = EvidenceObject::create($data);
        AuditLogger::log('evidence.uploaded', $evidence);

        return redirect()->route('evidence.show', $evidence)
            ->with('status', 'Dowód „' . $evidence->title . '" został dodany.');
    }

    public function show(EvidenceObject $evidence): View
    {
        abort_unless(auth()->user()->can('evidence.view'), 403);

        $evidence->load('uploader', 'client', 'links');

        return view('evidence.show', compact('evidence'));
    }

    public function edit(EvidenceObject $evidence): View
    {
        abort_unless(auth()->user()->can('evidence.update'), 403);

        if ($evidence->is_immutable) {
            return redirect()->route('evidence.show', $evidence)
                ->with('error', 'Ten dowód jest niezmienialny — edycja jest zablokowana.');
        }

        $clients = Client::where('is_active', true)->orderBy('name')->get();

        return view('evidence.form', [
            'evidence' => $evidence,
            'clients'  => $clients,
        ]);
    }

    public function update(Request $request, EvidenceObject $evidence): RedirectResponse
    {
        abort_unless(auth()->user()->can('evidence.update'), 403);

        if ($evidence->is_immutable) {
            return redirect()->route('evidence.show', $evidence)
                ->with('error', 'Ten dowód jest niezmienialny — edycja jest zablokowana.');
        }

        $this->mergeTagsFromRaw($request);
        $data = $this->validateEvidence($request);

        $evidence->update($data);
        AuditLogger::log('evidence.updated', $evidence);

        return redirect()->route('evidence.show', $evidence)
            ->with('status', 'Dowód zaktualizowany.');
    }

    public function destroy(EvidenceObject $evidence): RedirectResponse
    {
        abort_unless(auth()->user()->can('evidence.delete'), 403);

        if ($evidence->is_immutable) {
            return redirect()->route('evidence.show', $evidence)
                ->with('error', 'Ten dowód jest niezmienialny — usuwanie jest zablokowane.');
        }

        AuditLogger::log('evidence.deleted', $evidence);
        $evidence->delete();

        return redirect()->route('evidence.index')
            ->with('status', 'Dowód został usunięty.');
    }

    // ── helpers ────────────────────────────────────────────────────────────────

    private function mergeTagsFromRaw(Request $request): void
    {
        if ($request->has('tags_raw')) {
            $raw  = $request->string('tags_raw')->toString();
            $tags = array_values(array_filter(array_map('trim', explode(',', $raw))));
            $request->merge(['tags' => $tags]);
        }
    }

    private function validateEvidence(Request $request): array
    {
        return $request->validate([
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'classification'  => ['required', 'in:Public,Internal,Confidential,Restricted'],
            'tags'            => ['nullable', 'array'],
            'tags.*'          => ['string'],
            'valid_from'      => ['nullable', 'date'],
            'valid_until'     => ['nullable', 'date', 'after_or_equal:valid_from'],
            'retention_until' => ['nullable', 'date'],
            'client_id'       => ['nullable', 'exists:clients,id'],
            'original_filename' => ['nullable', 'string', 'max:255'],
            'sha256'          => ['nullable', 'string', 'max:64'],
            'is_immutable'    => ['boolean'],
        ]);
    }
}
