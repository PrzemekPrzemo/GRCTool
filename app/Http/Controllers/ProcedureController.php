<?php

namespace App\Http\Controllers;

use App\Models\EvidenceLink;
use App\Models\EvidenceObject;
use App\Models\Procedure;
use App\Models\ProcedureStep;
use App\Services\AuditLogger;
use App\Services\DocxTextExtractor;
use App\Services\EvidenceUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProcedureController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->can('policy.view'), 403);

        $procedures = Procedure::withCount('steps')->orderBy('code')->get();

        return view('procedure.index', compact('procedures'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('policy.create'), 403);

        return view('procedure.form', ['procedure' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('policy.create'), 403);

        $data = $this->validateProcedure($request);
        $this->applyDocxImport($request, $data);

        $procedure = Procedure::create($data);
        $this->syncSteps($request, $procedure);
        $this->maybeAttachUploadedSource($request, $procedure);
        AuditLogger::log('procedure.created', $procedure);

        return redirect()->route('procedures.show', $procedure)->with('success', 'Procedura dodana.');
    }

    public function show(Procedure $procedure): View
    {
        abort_unless(auth()->user()->can('policy.view'), 403);

        $procedure->load('steps', 'documentLinks.evidence');

        return view('procedure.show', compact('procedure'));
    }

    public function edit(Procedure $procedure): View
    {
        abort_unless(auth()->user()->can('policy.update'), 403);

        $procedure->load('steps');

        return view('procedure.form', compact('procedure'));
    }

    public function update(Request $request, Procedure $procedure): RedirectResponse
    {
        abort_unless(auth()->user()->can('policy.update'), 403);

        $data = $this->validateProcedure($request, $procedure);
        $this->applyDocxImport($request, $data);

        $procedure->update($data);
        $this->syncSteps($request, $procedure);
        $this->maybeAttachUploadedSource($request, $procedure);
        AuditLogger::log('procedure.updated', $procedure);

        return redirect()->route('procedures.show', $procedure)->with('success', 'Zapisano zmiany.');
    }

    public function attachDocument(Request $request, Procedure $procedure): RedirectResponse
    {
        abort_unless(auth()->user()->can('policy.update'), 403);

        if ($request->hasFile('file')) {
            $data = $request->validate([
                'title' => ['nullable', 'string', 'max:255'],
                'file' => ['required', 'file', 'max:20480'],
            ]);

            $evidence = app(EvidenceUploadService::class)->store($request->file('file'), 'procedure-documents', $data['title'] ?? null);
        } else {
            $data = $request->validate([
                'title' => ['nullable', 'string', 'max:255'],
                'drive_url' => ['required', 'url', 'max:1024'],
            ]);

            $evidence = EvidenceObject::create([
                'title' => $data['title'] ?? $procedure->title,
                'original_filename' => $data['title'] ?? $procedure->title,
                'source' => 'drive_link',
                'external_provider' => 'google_drive',
                'external_url' => $data['drive_url'],
                'uploaded_by' => auth()->id(),
            ]);
        }

        EvidenceLink::create([
            'evidence_id' => $evidence->id,
            'linkable_type' => Procedure::class,
            'linkable_id' => $procedure->id,
            'relation_role' => 'procedure_document',
        ]);

        AuditLogger::log('procedure.document_attached', $procedure, ['evidence_id' => $evidence->id]);

        return back()->with('success', 'Dokument podpięty.');
    }

    public function detachDocument(Procedure $procedure, EvidenceLink $document): RedirectResponse
    {
        abort_unless(auth()->user()->can('policy.update'), 403);
        abort_unless($document->linkable_type === Procedure::class && $document->linkable_id === $procedure->id, 404);

        $document->delete();
        AuditLogger::log('procedure.document_detached', $procedure);

        return back()->with('success', 'Dokument odpięty.');
    }

    public function downloadDocument(Procedure $procedure, EvidenceLink $document): StreamedResponse
    {
        abort_unless(auth()->user()->can('policy.view'), 403);
        abort_unless($document->linkable_type === Procedure::class && $document->linkable_id === $procedure->id, 404);

        $evidence = $document->evidence;
        abort_unless($evidence && $evidence->source === 'upload' && $evidence->storage_path, 404);

        return Storage::disk('local')->download($evidence->storage_path, $evidence->original_filename);
    }

    private function validateProcedure(Request $request, ?Procedure $procedure = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:32', Rule::unique('procedures', 'code')->ignore($procedure)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'policy_ref' => ['nullable', 'string', 'max:64'],
            'related_model' => ['nullable', 'string', 'max:64'],
            'owner_role' => ['nullable', 'string', 'max:255'],
            'version' => ['required', 'string', 'max:16'],
            'effective_from' => ['nullable', 'date'],
            'status' => ['required', 'in:Draft,Approved,Active,Retired'],
            'source_document' => ['nullable', 'file', 'mimes:docx', 'max:20480'],
            'steps' => ['nullable', 'array'],
            'steps.*.description' => ['required_with:steps', 'string'],
            'steps.*.owner_role' => ['nullable', 'string', 'max:255'],
            'steps.*.sla' => ['nullable', 'string', 'max:255'],
            'steps.*.tool' => ['nullable', 'string', 'max:255'],
        ]);

        unset($data['source_document'], $data['steps']);

        return $data;
    }

    /**
     * Jeśli formularz zawiera wgrany plik .docx, jego treść zastępuje pole
     * description — to jest sedno "importu" procedury z Worda. Błąd
     * ekstrakcji nie blokuje zapisu — plik i tak zostanie podpięty jako
     * załącznik przez maybeAttachUploadedSource().
     */
    private function applyDocxImport(Request $request, array &$data): void
    {
        if (! $request->hasFile('source_document')) {
            return;
        }

        try {
            $data['description'] = app(DocxTextExtractor::class)->extract($request->file('source_document')->getRealPath());
        } catch (\Throwable $e) {
            session()->flash('warning', 'Nie udało się odczytać treści z pliku Word: '.$e->getMessage().' Plik zostanie mimo to podpięty jako załącznik.');
        }
    }

    private function maybeAttachUploadedSource(Request $request, Procedure $procedure): void
    {
        if (! $request->hasFile('source_document')) {
            return;
        }

        $evidence = app(EvidenceUploadService::class)->store($request->file('source_document'), 'procedure-documents', $procedure->title);

        EvidenceLink::create([
            'evidence_id' => $evidence->id,
            'linkable_type' => Procedure::class,
            'linkable_id' => $procedure->id,
            'relation_role' => 'procedure_document',
        ]);
    }

    /**
     * Kroki nie mają zewnętrznych powiązań, więc zamiast diffować, przy
     * każdym zapisie usuwamy stare i tworzymy od nowa z przesłanej listy —
     * prostsze i wystarczające dla tej encji.
     */
    private function syncSteps(Request $request, Procedure $procedure): void
    {
        $steps = $request->input('steps', []);

        $procedure->steps()->delete();

        foreach (array_values($steps) as $i => $step) {
            if (blank($step['description'] ?? null)) {
                continue;
            }

            ProcedureStep::create([
                'procedure_id' => $procedure->id,
                'step_no' => $i + 1,
                'description' => $step['description'],
                'owner_role' => $step['owner_role'] ?? null,
                'sla' => $step['sla'] ?? null,
                'tool' => $step['tool'] ?? null,
            ]);
        }
    }
}
