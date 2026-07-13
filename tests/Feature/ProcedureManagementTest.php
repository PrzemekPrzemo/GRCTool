<?php

use App\Models\Procedure;
use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->assignRole('ciso');
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());
});

function makeProcedure(array $overrides = []): Procedure
{
    return Procedure::create(array_merge([
        'code' => 'PROC-TEST-'.random_int(1000, 999999),
        'title' => 'Testowa procedura',
        'version' => '1.0',
        'status' => 'Approved',
    ], $overrides));
}

it('creates a procedure with steps', function (): void {
    $this->post('/procedures', [
        'code' => 'PROC-2026-001',
        'title' => 'Procedura testowa',
        'version' => '1.0',
        'status' => 'Draft',
        'steps' => [
            ['description' => 'Krok pierwszy', 'owner_role' => 'CSO', 'sla' => '24h', 'tool' => 'Jira'],
            ['description' => 'Krok drugi'],
        ],
    ])->assertRedirect();

    $procedure = Procedure::where('code', 'PROC-2026-001')->firstOrFail();
    expect($procedure->steps)->toHaveCount(2);
    expect($procedure->steps->first()->step_no)->toBe(1);
    expect($procedure->steps->first()->owner_role)->toBe('CSO');
});

it('requires a unique code', function (): void {
    makeProcedure(['code' => 'PROC-DUP-001']);

    $this->post('/procedures', [
        'code' => 'PROC-DUP-001',
        'title' => 'Duplikat',
        'version' => '1.0',
        'status' => 'Draft',
    ])->assertSessionHasErrors('code');
});

it('updating a procedure replaces its step list', function (): void {
    $procedure = makeProcedure();
    $procedure->steps()->create(['step_no' => 1, 'description' => 'Stary krok']);

    $this->put("/procedures/{$procedure->id}", [
        'code' => $procedure->code,
        'title' => $procedure->title,
        'version' => $procedure->version,
        'status' => $procedure->status,
        'steps' => [
            ['description' => 'Nowy krok A'],
            ['description' => 'Nowy krok B'],
        ],
    ])->assertRedirect();

    $procedure->refresh();
    expect($procedure->steps)->toHaveCount(2);
    expect($procedure->steps->pluck('description')->all())->toBe(['Nowy krok A', 'Nowy krok B']);
});

it('creating a procedure with an uploaded .docx pre-fills the description and attaches the file', function (): void {
    $file = makeDocxFile(docxParagraphs(['Treść procedury z Worda.']));

    $this->post('/procedures', [
        'code' => 'PROC-DOCX-001',
        'title' => 'Procedura z importu',
        'version' => '1.0',
        'status' => 'Draft',
        'source_document' => $file,
    ])->assertRedirect();

    $procedure = Procedure::where('code', 'PROC-DOCX-001')->firstOrFail();
    expect($procedure->description)->toBe('Treść procedury z Worda.');
    expect($procedure->documentLinks()->count())->toBe(1);
    expect($procedure->documentLinks()->first()->evidence->source)->toBe('upload');
});

it('attaches a Google Drive link to a procedure without any API configuration', function (): void {
    $procedure = makeProcedure();

    $this->post("/procedures/{$procedure->id}/documents", [
        'drive_url' => 'https://drive.google.com/file/d/proc123/view',
    ])->assertRedirect();

    expect($procedure->documentLinks()->count())->toBe(1);
    expect($procedure->documentLinks()->first()->evidence->source)->toBe('drive_link');
});

it('attaches an uploaded file to a procedure and can download it', function (): void {
    $procedure = makeProcedure();
    $file = UploadedFile::fake()->create('procedura.pdf', 10, 'application/pdf');

    $this->post("/procedures/{$procedure->id}/documents", ['file' => $file])->assertRedirect();

    $link = $procedure->documentLinks()->first();
    $this->get("/procedures/{$procedure->id}/documents/{$link->id}/download")->assertOk();
});

it('detaches a document from a procedure', function (): void {
    $procedure = makeProcedure();
    $this->post("/procedures/{$procedure->id}/documents", ['drive_url' => 'https://drive.google.com/file/d/x/view']);
    $link = $procedure->documentLinks()->first();

    $this->delete("/procedures/{$procedure->id}/documents/{$link->id}")->assertRedirect();

    expect($procedure->documentLinks()->count())->toBe(0);
});

it('non-privileged user cannot create or edit procedures', function (): void {
    $user = User::factory()->create(['two_factor_confirmed_at' => now()]);
    $user->assignRole('external_auditor');
    $this->actingAs($user);

    $this->get('/procedures/create')->assertForbidden();

    $procedure = makeProcedure();
    $this->get("/procedures/{$procedure->id}/edit")->assertForbidden();
});
