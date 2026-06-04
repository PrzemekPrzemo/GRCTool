<?php

namespace App\Http\Controllers;

use App\Models\AnswerLibrary;
use App\Models\AnswerLibraryVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnswerLibraryController extends Controller
{
    public function index(Request $request): View
    {
        $q = AnswerLibrary::query()->with('reviewer');

        if ($search = $request->string('q')->trim()->toString()) {
            $q->where(function ($w) use ($search): void {
                $w->where('canonical_question', 'like', "%$search%")
                    ->orWhere('canonical_answer_short', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%");
            });
        }
        if ($conf = $request->string('confidentiality')->toString()) {
            $q->where('confidentiality_level', $conf);
        }

        $answers = $q->orderByDesc('usage_count')->orderBy('code')->paginate(50)->withQueryString();
        $needsReview = AnswerLibrary::whereDate('next_review_due', '<=', now()->addDays(30))->count();

        return view('answer_library.index', compact('answers', 'needsReview'));
    }

    public function create(): View
    {
        return view('answer_library.form', ['answer' => new AnswerLibrary]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAnswer($request);
        $answer = DB::transaction(function () use ($data) {
            $a = AnswerLibrary::create($data);
            $this->snapshotVersion($a, 'created');

            return $a;
        });

        return redirect()->route('answer-library.show', $answer)->with('status', "Odpowiedź {$answer->code} dodana.");
    }

    public function show(AnswerLibrary $answer): View
    {
        $answer->load('reviewer', 'versions.author');

        return view('answer_library.show', compact('answer'));
    }

    public function edit(AnswerLibrary $answer): View
    {
        return view('answer_library.form', compact('answer'));
    }

    public function update(Request $request, AnswerLibrary $answer): RedirectResponse
    {
        $data = $this->validateAnswer($request);
        DB::transaction(function () use ($answer, $data, $request): void {
            $answer->update([...$data, 'version' => $answer->version + 1]);
            $this->snapshotVersion($answer, 'updated', $request->string('change_reason')->toString() ?: null);
        });

        return redirect()->route('answer-library.show', $answer)->with('status', 'Zaktualizowano.');
    }

    public function export(Request $request): Response|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless(auth()->user()->can('answer-library.view'), 403);

        $format = $request->string('format')->lower()->toString() ?: 'json';
        $minLevel = $request->string('level')->toString() ?: 'Internal';

        $allowedLevels = match ($minLevel) {
            'Public'   => ['Public'],
            'Internal' => ['Public', 'Internal'],
            default    => ['Public', 'Internal', 'NDA-only', 'Confidential'],
        };

        $entries = AnswerLibrary::where('is_active', true)
            ->whereIn('confidentiality_level', $allowedLevels)
            ->orderBy('code')
            ->get(['code', 'canonical_question', 'aliases', 'canonical_answer_short', 'canonical_answer_long', 'tags', 'frameworks', 'confidentiality_level', 'version', 'last_reviewed_at']);

        $timestamp = now()->format('Ymd_His');

        if ($format === 'csv') {
            $filename = "answer_library_{$timestamp}.csv";

            return response()->streamDownload(function () use ($entries): void {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['code', 'canonical_question', 'aliases', 'canonical_answer_short', 'canonical_answer_long', 'tags', 'frameworks', 'confidentiality_level', 'version', 'last_reviewed_at']);
                foreach ($entries as $e) {
                    fputcsv($out, [
                        $e->code,
                        $e->canonical_question,
                        implode(' | ', $e->aliases ?? []),
                        $e->canonical_answer_short ?? '',
                        $e->canonical_answer_long ?? '',
                        implode(', ', $e->tags ?? []),
                        implode(', ', $e->frameworks ?? []),
                        $e->confidentiality_level,
                        $e->version,
                        $e->last_reviewed_at?->format('Y-m-d') ?? '',
                    ]);
                }
                fclose($out);
            }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        // JSON — NotebookLM / vector DB ready format
        $payload = [
            'exported_at'  => now()->toIso8601String(),
            'total'        => $entries->count(),
            'min_level'    => $minLevel,
            'entries'      => $entries->map(fn ($e) => [
                'id'                    => $e->code,
                'question'              => $e->canonical_question,
                'aliases'               => $e->aliases ?? [],
                'answer_short'          => $e->canonical_answer_short,
                'answer_full'           => $e->canonical_answer_long,
                'tags'                  => $e->tags ?? [],
                'frameworks'            => $e->frameworks ?? [],
                'confidentiality_level' => $e->confidentiality_level,
                'version'               => $e->version,
                'last_reviewed_at'      => $e->last_reviewed_at?->format('Y-m-d'),
            ])->values(),
        ];

        return response(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), 200, [
            'Content-Type'        => 'application/json; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"answer_library_{$timestamp}.json\"",
        ]);
    }

    public function review(AnswerLibrary $answer): RedirectResponse
    {
        $answer->update([
            'last_reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'next_review_due' => now()->addYear(),
        ]);

        return back()->with('status', 'Review zarejestrowany.');
    }

    private function validateAnswer(Request $request): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:32'],
            'canonical_question' => ['required', 'string', 'max:1024'],
            'aliases_text' => ['nullable', 'string'], // jedna na linię
            'canonical_answer_short' => ['nullable', 'string', 'max:500'],
            'canonical_answer_long' => ['required', 'string'],
            'tags_text' => ['nullable', 'string'],
            'frameworks_text' => ['nullable', 'string'],
            'confidentiality_level' => ['required', 'in:Public,NDA-only,Internal,Confidential'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['aliases'] = $this->splitLines($data['aliases_text'] ?? '');
        $data['tags'] = $this->splitLines($data['tags_text'] ?? '');
        $data['frameworks'] = $this->splitLines($data['frameworks_text'] ?? '');
        unset($data['aliases_text'], $data['tags_text'], $data['frameworks_text']);

        return $data;
    }

    /** @return string[] */
    private function splitLines(string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[\r\n,;]+/', $text))));
    }

    private function snapshotVersion(AnswerLibrary $answer, string $action, ?string $reason = null): void
    {
        $next = ($answer->versions()->max('version_number') ?? 0) + 1;
        AnswerLibraryVersion::create([
            'answer_id' => $answer->id,
            'version_number' => $next,
            'snapshot' => $answer->fresh()->toArray(),
            'changed_by' => auth()->id(),
            'change_reason' => $reason ?: $action,
            'changed_at' => now(),
        ]);
    }
}
