<?php

namespace App\Http\Controllers;

use App\Models\AnswerLibrary;
use App\Models\Client;
use App\Models\QuestionnaireQuestion;
use App\Models\QuestionnaireTemplate;
use App\Models\SecurityQuestionnaire;
use App\Services\AuditLogger;
use App\Services\QuestionMatchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InboundQuestionnaireController extends Controller
{
    public function __construct(private QuestionMatchingService $matcher) {}

    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('rfp.view'), 403);

        $q = SecurityQuestionnaire::query()->where('direction', 'inbound')->with('client', 'template', 'owner');

        if ($status = $request->string('status')->toString()) {
            $q->where('status', $status);
        }
        if ($clientId = $request->integer('client_id')) {
            $q->where('client_id', $clientId);
        }

        $questionnaires = $q->orderByDesc('id')->paginate(25)->withQueryString();
        $clients = Client::orderBy('name')->get(['id', 'name']);

        return view('questionnaires.index', compact('questionnaires', 'clients'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('rfp.create'), 403);

        return view('questionnaires.form', [
            'questionnaire' => new SecurityQuestionnaire(['direction' => 'inbound']),
            'clients' => Client::orderBy('name')->get(),
            'templates' => QuestionnaireTemplate::where('direction', 'inbound')->orWhere('direction', 'both')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('rfp.create'), 403);

        $data = $request->validate([
            'name' => ['required', 'string'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'template_id' => ['nullable', 'exists:questionnaire_templates,id'],
            'received_at' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'questions_csv' => ['nullable', 'file', 'mimes:csv,txt'],
            'questions_text' => ['nullable', 'string'],
        ]);

        $code = sprintf('Q-%s-%04d', now()->format('Y'), SecurityQuestionnaire::count() + 1);

        $q = DB::transaction(function () use ($data, $code, $request) {
            $q = SecurityQuestionnaire::create([
                'code' => $code,
                'direction' => 'inbound',
                'name' => $data['name'],
                'client_id' => $data['client_id'] ?? null,
                'template_id' => $data['template_id'] ?? null,
                'received_at' => $data['received_at'] ?? now()->toDateString(),
                'due_date' => $data['due_date'] ?? null,
                'status' => 'Received',
                'owner_id' => auth()->id(),
            ]);

            // Pull questions from template, CSV, or free-text
            $count = 0;
            if (! empty($data['template_id'])) {
                $template = QuestionnaireTemplate::with('questions')->find($data['template_id']);
                foreach ($template->questions as $i => $tq) {
                    QuestionnaireQuestion::create([
                        'questionnaire_id' => $q->id,
                        'template_question_id' => $tq->id,
                        'original_text' => $tq->question_text,
                        'category' => $tq->category,
                        'expected_answer_type' => $tq->expected_answer_type,
                        'order' => $i,
                        'status' => 'Pending',
                    ]);
                    $count++;
                }
            }

            if ($request->hasFile('questions_csv')) {
                $count += $this->importFromCsv($q, $request->file('questions_csv')->getRealPath(), $count);
            }

            if (! empty($data['questions_text'])) {
                $lines = preg_split('/\r?\n/', $data['questions_text']);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }
                    QuestionnaireQuestion::create([
                        'questionnaire_id' => $q->id,
                        'original_text' => $line,
                        'order' => $count++,
                        'status' => 'Pending',
                    ]);
                }
            }

            $q->refreshCounts();

            return $q;
        });

        return redirect()->route('questionnaires.show', $q)->with('status', "Ankieta {$q->code} utworzona z {$q->total_questions} pytaniami.");
    }

    public function show(SecurityQuestionnaire $questionnaire): View
    {
        abort_unless(auth()->user()->can('rfp.view'), 403);

        if ($questionnaire->direction !== 'inbound') {
            abort(404);
        }
        $questionnaire->load('client', 'template', 'owner', 'questions.mappedAnswer', 'questions.reviewer', 'questions.flaggedBy', 'finalExport');

        // Sugestie z treści polityk — tylko dla pytań bez jeszcze dopasowanej odpowiedzi,
        // żeby nie liczyć tego niepotrzebnie dla już rozwiązanych pytań.
        $policySuggestions = [];
        foreach ($questionnaire->questions as $question) {
            if ($question->mapped_answer_id || in_array($question->status, ['Approved', 'Auto-filled'], true)) {
                continue;
            }
            $matches = $this->matcher->findPolicySuggestions($question->original_text, 2);
            if ($matches !== []) {
                $policySuggestions[$question->id] = $matches;
            }
        }

        return view('questionnaires.show', compact('questionnaire', 'policySuggestions'));
    }

    public function autoFill(SecurityQuestionnaire $questionnaire): RedirectResponse
    {
        abort_unless(auth()->user()->can('rfp.update'), 403);

        if ($questionnaire->direction !== 'inbound') {
            abort(404);
        }

        $autoFilled = 0;
        foreach ($questionnaire->questions()->where('status', 'Pending')->get() as $question) {
            $matches = $this->matcher->findMatches($question->original_text, $question->category ? [$question->category] : [], 1);
            if (empty($matches)) {
                continue;
            }
            $top = $matches[0];
            if ($top['score'] < QuestionMatchingService::AUTO_FILL_THRESHOLD) {
                continue;
            }

            /** @var AnswerLibrary $answer */
            $answer = $top['answer'];
            $question->update([
                'mapped_answer_id' => $answer->id,
                'confidence_score' => $top['score'],
                'answer_text' => $answer->canonical_answer_long,
                'status' => 'Auto-filled',
            ]);
            $answer->increment('usage_count');
            $autoFilled++;
        }

        $questionnaire->refreshCounts();
        AuditLogger::log('questionnaire_autofilled', $questionnaire, ['auto_filled' => $autoFilled]);

        return back()->with('status', "Auto-fill: dopasowano {$autoFilled} odpowiedzi (threshold ".QuestionMatchingService::AUTO_FILL_THRESHOLD.').');
    }

    public function addQuestion(Request $request, SecurityQuestionnaire $questionnaire): RedirectResponse
    {
        abort_unless(auth()->user()->can('rfp.create'), 403);

        $data = $request->validate([
            'original_text' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:64'],
        ]);

        $nextOrder = ($questionnaire->questions()->max('order') ?? -1) + 1;
        QuestionnaireQuestion::create([
            'questionnaire_id' => $questionnaire->id,
            'original_text' => $data['original_text'],
            'category' => $data['category'] ?? null,
            'order' => $nextOrder,
            'status' => 'Pending',
        ]);
        $questionnaire->refreshCounts();
        AuditLogger::log('questionnaire_question_added', $questionnaire);

        return back()->with('status', 'Pytanie dodane.');
    }

    public function flagForCso(Request $request, SecurityQuestionnaire $questionnaire, QuestionnaireQuestion $question): RedirectResponse
    {
        abort_unless(auth()->user()->can('rfp.create'), 403);

        $data = $request->validate([
            'flag_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $question->update([
            'status' => 'Needs-Info',
            'flagged_by' => auth()->id(),
            'flagged_at' => now(),
            'flag_note' => $data['flag_note'] ?? null,
        ]);
        $questionnaire->refreshCounts();
        AuditLogger::log('questionnaire_question_flagged', $questionnaire, ['question_id' => $question->id]);

        return back()->with('status', 'Zgłoszono do CSO — pytanie oznaczone jako "Needs-Info".');
    }

    public function updateQuestion(Request $request, SecurityQuestionnaire $questionnaire, QuestionnaireQuestion $question): RedirectResponse
    {
        abort_unless(auth()->user()->can('rfp.update'), 403);

        $data = $request->validate([
            'answer_text' => ['required', 'string'],
            'mapped_answer_id' => ['nullable', 'exists:answer_library,id'],
        ]);

        $question->update([
            ...$data,
            'status' => 'Reviewed',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        $questionnaire->refreshCounts();

        return back()->with('status', 'Odpowiedź zaktualizowana.');
    }

    public function approveQuestion(SecurityQuestionnaire $questionnaire, QuestionnaireQuestion $question): RedirectResponse
    {
        abort_unless(auth()->user()->can('rfp.update'), 403);

        $question->update([
            'status' => 'Approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        $questionnaire->refreshCounts();

        return back()->with('status', 'Pytanie zatwierdzone.');
    }

    public function export(SecurityQuestionnaire $questionnaire): RedirectResponse
    {
        abort_unless(auth()->user()->can('rfp.update'), 403);

        if ($questionnaire->direction !== 'inbound') {
            abort(404);
        }

        $questionnaire->update(['status' => 'Sent', 'completed_at' => now()->toDateString()]);
        AuditLogger::log('questionnaire_exported', $questionnaire);

        return back()->with('status', 'Ankieta oznaczona jako wysłana. PDF export dostępny w module Reports (TODO V1).');
    }

    public function edit(SecurityQuestionnaire $questionnaire): View
    {
        abort_unless(auth()->user()->can('rfp.update'), 403);

        return view('questionnaires.form', [
            'questionnaire' => $questionnaire,
            'clients' => Client::orderBy('name')->get(),
            'templates' => QuestionnaireTemplate::where('direction', 'inbound')->orWhere('direction', 'both')->get(),
        ]);
    }

    public function update(Request $request, SecurityQuestionnaire $questionnaire): RedirectResponse
    {
        abort_unless(auth()->user()->can('rfp.update'), 403);

        $data = $request->validate([
            'name' => ['required', 'string'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'string'],
        ]);
        $questionnaire->update($data);

        return back()->with('status', 'Zaktualizowano.');
    }

    private function importFromCsv(SecurityQuestionnaire $q, string $path, int $startOrder): int
    {
        $h = fopen($path, 'r');
        $headers = array_map('strtolower', fgetcsv($h) ?: []);
        $count = 0;
        while (($row = fgetcsv($h)) !== false) {
            $r = array_combine($headers, $row);
            $text = $r['question'] ?? $r['question_text'] ?? $r['text'] ?? null;
            if (! $text) {
                continue;
            }
            QuestionnaireQuestion::create([
                'questionnaire_id' => $q->id,
                'original_text' => $text,
                'category' => $r['category'] ?? null,
                'expected_answer_type' => $r['type'] ?? 'text',
                'order' => $startOrder + $count,
                'status' => 'Pending',
            ]);
            $count++;
        }
        fclose($h);

        return $count;
    }
}
