<?php

use App\Models\AnswerLibrary;
use App\Models\Policy;
use App\Models\QuestionnaireQuestion;
use App\Models\SecurityQuestionnaire;
use App\Models\User;

function actingAsRole(string $role): User
{
    $user = User::factory()->create(['two_factor_confirmed_at' => now()]);
    $user->assignRole($role);
    test()->actingAs($user);

    return $user;
}

function makeAnswer(array $overrides = []): AnswerLibrary
{
    return AnswerLibrary::create(array_merge([
        'code' => 'A-TEST-'.random_int(1000, 999999),
        'canonical_question' => 'Czy stosujecie szyfrowanie danych w spoczynku?',
        'canonical_answer_long' => 'Tak, wszystkie dane RESTRICTED i CONFIDENTIAL są szyfrowane AES-256.',
        'confidentiality_level' => 'Internal',
    ], $overrides));
}

function makeInboundQuestionnaire(array $overrides = []): SecurityQuestionnaire
{
    return SecurityQuestionnaire::create(array_merge([
        'code' => 'Q-TEST-'.random_int(1000, 999999),
        'direction' => 'inbound',
        'name' => 'Testowa ankieta RFP',
        'status' => 'Received',
    ], $overrides));
}

// ─────────────────────────────────────────────────────────────────────────────
// Uprawnienia rfp.*
// ─────────────────────────────────────────────────────────────────────────────

it('sales role can view AnswerLibrary but not create or edit entries', function (): void {
    actingAsRole('sales');
    $answer = makeAnswer();

    $this->get('/answer-library')->assertOk();
    $this->get("/answer-library/{$answer->id}")->assertOk();
    $this->get('/answer-library/create')->assertForbidden();
    $this->post('/answer-library', ['code' => 'X'])->assertForbidden();
    $this->get("/answer-library/{$answer->id}/edit")->assertForbidden();
});

it('ciso role has full AnswerLibrary access including create and edit', function (): void {
    actingAsRole('ciso');

    $this->get('/answer-library')->assertOk();
    $this->get('/answer-library/create')->assertOk();

    $this->post('/answer-library', [
        'code' => 'A-CISO-001',
        'canonical_question' => 'Testowe pytanie?',
        'canonical_answer_long' => 'Testowa odpowiedź.',
        'confidentiality_level' => 'Internal',
    ])->assertRedirect();

    expect(AnswerLibrary::where('code', 'A-CISO-001')->exists())->toBeTrue();
});

it('user without rfp permission cannot access AnswerLibrary or questionnaires at all', function (): void {
    $user = User::factory()->create(['two_factor_confirmed_at' => now()]);
    $user->assignRole('external_auditor');
    $this->actingAs($user);

    $this->get('/answer-library')->assertForbidden();
    $this->get('/questionnaires')->assertForbidden();
});

it('sales role can view and initiate questionnaires but cannot auto-fill or approve', function (): void {
    actingAsRole('sales');
    $questionnaire = makeInboundQuestionnaire();

    $this->get('/questionnaires')->assertOk();
    $this->get("/questionnaires/{$questionnaire->id}")->assertOk();
    $this->post("/questionnaires/{$questionnaire->id}/auto-fill")->assertForbidden();
    $this->post('/questionnaires', ['name' => 'Nowa ankieta od klienta'])->assertRedirect();
});

// ─────────────────────────────────────────────────────────────────────────────
// Dodawanie własnych pytań + zgłaszanie do CSO
// ─────────────────────────────────────────────────────────────────────────────

it('sales can add a client question to an existing questionnaire', function (): void {
    actingAsRole('sales');
    $questionnaire = makeInboundQuestionnaire();

    $this->post("/questionnaires/{$questionnaire->id}/questions", [
        'original_text' => 'Czy macie certyfikat ISO 27001?',
        'category' => 'Certyfikacja',
    ])->assertRedirect();

    $question = QuestionnaireQuestion::where('questionnaire_id', $questionnaire->id)->firstOrFail();
    expect($question->original_text)->toBe('Czy macie certyfikat ISO 27001?');
    expect($question->status)->toBe('Pending');
    expect($questionnaire->fresh()->total_questions)->toBe(1);
});

it('sales can flag a question as needing CSO input, and ciso can then answer it', function (): void {
    $sales = actingAsRole('sales');
    $questionnaire = makeInboundQuestionnaire();
    $question = QuestionnaireQuestion::create([
        'questionnaire_id' => $questionnaire->id,
        'original_text' => 'Jaki jest wasz RTO/RPO dla systemów produkcyjnych?',
        'status' => 'Pending',
        'order' => 0,
    ]);

    $this->post("/questionnaires/{$questionnaire->id}/questions/{$question->id}/flag", [
        'flag_note' => 'Klient pyta o konkretne liczby, nie mam tej wiedzy.',
    ])->assertRedirect();

    $question->refresh();
    expect($question->status)->toBe('Needs-Info');
    expect($question->flagged_by)->toBe($sales->id);
    expect($question->flag_note)->toBe('Klient pyta o konkretne liczby, nie mam tej wiedzy.');
    expect($question->flagged_at)->not->toBeNull();

    // sales cannot itself provide the final answer
    $this->post("/questionnaires/{$questionnaire->id}/questions/{$question->id}/answer", [
        'answer_text' => 'Próba odpowiedzi przez sales',
    ])->assertForbidden();

    // CSO steps in and answers
    $ciso = User::where('email', 'ciso@grc.local')->firstOrFail();
    $ciso->forceFill(['two_factor_confirmed_at' => now()])->save();
    $this->actingAs($ciso);
    $this->post("/questionnaires/{$questionnaire->id}/questions/{$question->id}/answer", [
        'answer_text' => 'RTO: 4h, RPO: 1h dla systemów krytycznych.',
    ])->assertRedirect();

    $question->refresh();
    expect($question->answer_text)->toBe('RTO: 4h, RPO: 1h dla systemów krytycznych.');
    expect($question->status)->toBe('Reviewed');
});

// ─────────────────────────────────────────────────────────────────────────────
// Powiązanie AnswerLibrary <-> Policy
// ─────────────────────────────────────────────────────────────────────────────

it('links an AnswerLibrary entry to source policies and displays them', function (): void {
    actingAsRole('ciso');
    $policy = Policy::create(['code' => 'POL-TEST-RFP-001', 'title' => 'Polityka szyfrowania', 'status' => 'Active']);

    $this->post('/answer-library', [
        'code' => 'A-LINK-001',
        'canonical_question' => 'Czy szyfrujecie dane?',
        'canonical_answer_long' => 'Tak.',
        'confidentiality_level' => 'Internal',
        'policy_ids' => [$policy->id],
    ])->assertRedirect();

    $answer = AnswerLibrary::where('code', 'A-LINK-001')->firstOrFail();
    expect($answer->policy_ids)->toBe([$policy->id]);
    expect($answer->linkedPolicies()->pluck('id')->all())->toBe([$policy->id]);

    $this->get("/answer-library/{$answer->id}")->assertOk()->assertSee('POL-TEST-RFP-001');
});

// ─────────────────────────────────────────────────────────────────────────────
// Sugestie z treści polityk (QuestionMatchingService)
// ─────────────────────────────────────────────────────────────────────────────

it('shows policy-text suggestions on the questionnaire page for unanswered questions', function (): void {
    actingAsRole('ciso');
    Policy::create([
        'code' => 'POL-TEST-RFP-002',
        'title' => 'Polityka backupu i odtwarzania',
        'status' => 'Active',
        'description' => 'Kopie zapasowe wykonywane są codziennie, przechowywane przez 30 dni w izolowanej lokalizacji offline.',
    ]);

    $questionnaire = makeInboundQuestionnaire();
    QuestionnaireQuestion::create([
        'questionnaire_id' => $questionnaire->id,
        'original_text' => 'Jak często wykonujecie kopie zapasowe?',
        'status' => 'Pending',
        'order' => 0,
    ]);

    $this->get("/questionnaires/{$questionnaire->id}")
        ->assertOk()
        ->assertSee('Sugestie z polityk')
        ->assertSee('POL-TEST-RFP-002');
});

it('does not compute policy suggestions for already-approved questions', function (): void {
    actingAsRole('ciso');
    Policy::create([
        'code' => 'POL-TEST-RFP-003',
        'title' => 'Polityka haseł',
        'status' => 'Active',
        'description' => 'Minimalna długość hasła to 14 znaków, MFA jest obowiązkowe dla wszystkich kont.',
    ]);

    $questionnaire = makeInboundQuestionnaire();
    QuestionnaireQuestion::create([
        'questionnaire_id' => $questionnaire->id,
        'original_text' => 'Jaka jest wasza polityka haseł?',
        'status' => 'Approved',
        'answer_text' => 'Już odpowiedziane.',
        'order' => 0,
    ]);

    $this->get("/questionnaires/{$questionnaire->id}")
        ->assertOk()
        ->assertDontSee('Sugestie z polityk');
});
