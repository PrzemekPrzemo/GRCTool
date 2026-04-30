<?php

namespace App\Http\Controllers;

use App\Models\AnswerLibrary;
use App\Models\AnswerLibraryVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
