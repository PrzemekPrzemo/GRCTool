<?php

namespace App\Http\Controllers;

use App\Models\EvidenceLink;
use App\Models\EvidenceObject;
use App\Models\Policy;
use App\Models\PolicyAttestation;
use App\Models\PolicyVersion;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\GoogleDriveService;
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
        $this->recordVersion($policy, 'Utworzenie polityki');
        AuditLogger::log('policy.created', $policy);

        return redirect()->route('policies.show', $policy)->with('success', 'Polityka dodana.');
    }

    public function show(Policy $policy, GoogleDriveService $drive): View
    {
        abort_unless(auth()->user()->can('policy.view'), 403);

        $policy->load(['owner', 'approver', 'attestations.user', 'documentLinks.evidence', 'versions.author']);
        $userAttestation = $policy->attestations()
            ->where('user_id', auth()->id())
            ->where('policy_version', $policy->current_version)
            ->first();

        return view('policy.show', [
            'policy' => $policy,
            'userAttestation' => $userAttestation,
            'driveApiEnabled' => $drive->isApiEnabled(),
        ]);
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
        $this->recordVersion($policy, 'Edycja polityki');
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

        $this->recordVersion($policy, 'Zatwierdzenie polityki');
        AuditLogger::log('policy.approved', $policy);

        return back()->with('success', 'Polityka zatwierdzona i aktywna.');
    }

    public function showImport(): View
    {
        abort_unless(auth()->user()->can('policy.create'), 403);

        return view('policy.import');
    }

    /**
     * Format CSV: code,title,category,current_version,status,owner_email,effective_from,next_review_due,description,drive_url
     */
    public function import(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('policy.create'), 403);
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:10240']]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        $headers = array_map('strtolower', fgetcsv($handle));

        $count = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $r = array_combine($headers, $row);
            try {
                $code = trim((string) ($r['code'] ?? '')) ?: $this->generateCode();
                $policy = Policy::updateOrCreate(
                    ['code' => $code],
                    [
                        'title'            => $r['title'] ?? '',
                        'category'         => $r['category'] ?: null,
                        'current_version'  => $r['current_version'] ?: '1.0',
                        'status'           => $r['status'] ?: 'Draft',
                        'owner_id'         => ! empty($r['owner_email']) ? optional(User::where('email', $r['owner_email'])->first())->id : null,
                        'effective_from'   => $r['effective_from'] ?: null,
                        'next_review_due'  => $r['next_review_due'] ?: null,
                        'description'      => $r['description'] ?: null,
                    ],
                );

                if (! empty($r['drive_url'])) {
                    $this->attachDriveLink($policy, $r['drive_url'], $r['title'] ?? $policy->title);
                }

                $this->recordVersion($policy, 'Import zbiorczy CSV');
                $count++;
            } catch (\Throwable $e) {
                $errors[] = ($r['code'] ?? 'row?').' — '.$e->getMessage();
            }
        }
        fclose($handle);

        $msg = "Zaimportowano/zaktualizowano $count polityk.";
        if ($errors) {
            $msg .= ' Błędy: '.implode('; ', array_slice($errors, 0, 5));
        }

        AuditLogger::log('policy.bulk_imported', null, ['count' => $count]);

        return redirect()->route('policies.index')->with('success', $msg);
    }

    public function bulkEdit(Request $request): View
    {
        abort_unless(auth()->user()->can('policy.update'), 403);

        $ids = collect($request->query('ids', []))->filter()->map(fn ($v) => (int) $v)->values();
        $policies = Policy::whereIn('id', $ids)->orderBy('title')->get();

        return view('policy.bulk-edit', [
            'policies'   => $policies,
            'users'      => User::orderBy('name')->get(),
            'categories' => Policy::distinct()->pluck('category')->filter()->sort()->values(),
        ]);
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('policy.update'), 403);

        $data = $request->validate([
            'ids'             => ['required', 'array', 'min:1'],
            'ids.*'           => ['integer', 'exists:policies,id'],
            'status'          => ['nullable', 'in:Draft,Approved,Active,Retired'],
            'owner_id'        => ['nullable', 'exists:users,id'],
            'category'        => ['nullable', 'string', 'max:64'],
            'next_review_due' => ['nullable', 'date'],
        ]);

        $fields = [];
        if ($request->boolean('apply_status')) {
            $fields['status'] = $data['status'] ?? null;
        }
        if ($request->boolean('apply_owner')) {
            $fields['owner_id'] = $data['owner_id'] ?? null;
        }
        if ($request->boolean('apply_category')) {
            $fields['category'] = $data['category'] ?? null;
        }
        if ($request->boolean('apply_review')) {
            $fields['next_review_due'] = $data['next_review_due'] ?? null;
        }
        if ($request->boolean('apply_attestation')) {
            $fields['attestation_required'] = $request->boolean('attestation_required');
        }

        if (empty($fields)) {
            return back()->with('error', 'Nie zaznaczono żadnego pola do masowej zmiany.');
        }

        $policies = Policy::whereIn('id', $data['ids'])->get();
        foreach ($policies as $policy) {
            $before = $policy->only(array_keys($fields));
            $policy->update($fields);
            $this->recordVersion($policy, 'Masowa aktualizacja: '.implode(', ', array_keys($fields)));
            AuditLogger::log('policy.bulk_updated', $policy, ['before' => $before, 'after' => $fields]);
        }

        return redirect()->route('policies.index')->with('success', "Zaktualizowano masowo {$policies->count()} polityk.");
    }

    public function attachDocument(Request $request, Policy $policy): RedirectResponse
    {
        abort_unless(auth()->user()->can('policy.update'), 403);

        $data = $request->validate([
            'title'         => ['nullable', 'string', 'max:255'],
            'drive_url'     => ['required', 'url', 'max:1024'],
            'drive_file_id' => ['nullable', 'string', 'max:191'],
        ]);

        $evidence = $this->attachDriveLink($policy, $data['drive_url'], $data['title'] ?? null, $data['drive_file_id'] ?? null);

        AuditLogger::log('policy.document_attached', $policy, ['evidence_id' => $evidence->id]);

        return back()->with('success', 'Dokument podpięty.');
    }

    public function detachDocument(Policy $policy, EvidenceLink $document): RedirectResponse
    {
        abort_unless(auth()->user()->can('policy.update'), 403);
        abort_unless($document->linkable_type === Policy::class && $document->linkable_id === $policy->id, 404);

        $document->delete();
        AuditLogger::log('policy.document_detached', $policy);

        return back()->with('success', 'Dokument odpięty.');
    }

    public function syncDocument(Policy $policy, EvidenceLink $document, GoogleDriveService $drive): RedirectResponse
    {
        abort_unless(auth()->user()->can('policy.update'), 403);
        abort_unless($document->linkable_type === Policy::class && $document->linkable_id === $policy->id, 404);

        if (! $drive->isApiEnabled()) {
            return back()->with('error', 'Synchronizacja przez Google Drive API jest wyłączona. Włącz ją w Ustawienia → Google Drive.');
        }

        $evidence = $document->evidence;
        if (! $evidence?->external_file_id) {
            return back()->with('error', 'Ten dokument nie ma powiązanego ID pliku Google Drive — podaj je przy podpinaniu dokumentu.');
        }

        try {
            $meta = $drive->fetchFileMetadata($evidence->external_file_id);
        } catch (\Throwable $e) {
            return back()->with('error', 'Błąd synchronizacji z Google Drive: '.$e->getMessage());
        }

        $changed = $evidence->original_filename !== ($meta['name'] ?? $evidence->original_filename);
        $evidence->update([
            'original_filename'   => $meta['name'] ?? $evidence->original_filename,
            'external_url'        => $meta['webViewLink'] ?? $evidence->external_url,
            'external_synced_at'  => now(),
            'source'              => 'drive_api',
        ]);

        if ($changed) {
            $this->recordVersion($policy, 'Synchronizacja z Google Drive: zmiana dokumentu');
        }

        AuditLogger::log('policy.document_synced', $policy, ['evidence_id' => $evidence->id]);

        return back()->with('success', 'Zsynchronizowano z Google Drive.');
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

    private function attachDriveLink(Policy $policy, string $url, ?string $title = null, ?string $fileId = null): EvidenceObject
    {
        $evidence = EvidenceObject::create([
            'title'              => $title ?: $policy->title,
            'original_filename'  => $title ?: $policy->title,
            'source'             => $fileId ? 'drive_api' : 'drive_link',
            'external_provider'  => 'google_drive',
            'external_file_id'   => $fileId,
            'external_url'       => $url,
            'uploaded_by'        => auth()->id(),
        ]);

        EvidenceLink::create([
            'evidence_id'   => $evidence->id,
            'linkable_type' => Policy::class,
            'linkable_id'   => $policy->id,
            'relation_role' => 'policy_document',
        ]);

        return $evidence;
    }

    private function recordVersion(Policy $policy, ?string $reason = null): void
    {
        PolicyVersion::create([
            'policy_id'       => $policy->id,
            'version_number'  => $policy->current_version,
            'snapshot'        => $policy->only([
                'title', 'category', 'status', 'current_version',
                'effective_from', 'next_review_due', 'owner_id', 'description',
            ]),
            'changed_by'      => auth()->id(),
            'change_reason'   => $reason,
            'changed_at'      => now(),
        ]);
    }
}
