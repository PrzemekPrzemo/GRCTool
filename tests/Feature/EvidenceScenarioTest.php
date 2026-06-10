<?php

use App\Models\EvidenceObject;
use App\Models\ScenarioTemplate;
use App\Models\User;

// ─────────────────────────────────────────────────────────────────────────────
// BeforeEach: authenticate as admin with ciso role + MFA confirmed
// ─────────────────────────────────────────────────────────────────────────────

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->assignRole('ciso');
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());
});

// ─────────────────────────────────────────────────────────────────────────────
// Evidence: basic CRUD
// ─────────────────────────────────────────────────────────────────────────────

it('renders evidence index page', function (): void {
    $this->get('/evidence')
        ->assertOk();
});

it('creates an evidence object', function (): void {
    $countBefore = EvidenceObject::count();

    $response = $this->post('/evidence', [
        'title'          => 'Raport z audytu Q1 2026',
        'classification' => 'Confidential',
        'is_immutable'   => '0',
    ]);

    $response->assertRedirect();
    expect(EvidenceObject::count())->toBe($countBefore + 1);

    $evidence = EvidenceObject::latest()->first();
    expect($evidence->title)->toBe('Raport z audytu Q1 2026')
        ->and($evidence->classification)->toBe('Confidential');
});

it('shows evidence detail', function (): void {
    $evidence = EvidenceObject::create([
        'title'             => 'Dowód testowy',
        'classification'    => 'Internal',
        'original_filename' => 'test.pdf',
        'storage_path'      => 'manual/test-path',
        'mime_type'         => 'application/pdf',
        'size_bytes'        => 1024,
        'sha256'            => str_repeat('a', 64),
        'retention_until'   => now()->addYears(7)->toDateString(),
        'uploaded_by'       => User::where('email', 'admin@grc.local')->value('id'),
    ]);

    $this->get("/evidence/{$evidence->id}")
        ->assertOk();
});

// ─────────────────────────────────────────────────────────────────────────────
// Evidence: immutability
// ─────────────────────────────────────────────────────────────────────────────

it('blocks editing of immutable evidence', function (): void {
    $evidence = EvidenceObject::create([
        'title'             => 'Dowód niezmienialny',
        'classification'    => 'Restricted',
        'is_immutable'      => true,
        'original_filename' => 'immutable.pdf',
        'storage_path'      => 'manual/immutable-path',
        'mime_type'         => 'application/pdf',
        'size_bytes'        => 512,
        'sha256'            => str_repeat('b', 64),
        'retention_until'   => now()->addYears(7)->toDateString(),
        'uploaded_by'       => User::where('email', 'admin@grc.local')->value('id'),
    ]);

    // EvidenceController::edit() redirects back to show with error when is_immutable
    $this->get("/evidence/{$evidence->id}/edit")
        ->assertRedirect("/evidence/{$evidence->id}");
});

it('allows editing mutable evidence', function (): void {
    $evidence = EvidenceObject::create([
        'title'             => 'Dowód modyfikowalny',
        'classification'    => 'Internal',
        'is_immutable'      => false,
        'original_filename' => 'mutable.pdf',
        'storage_path'      => 'manual/mutable-path',
        'mime_type'         => 'application/pdf',
        'size_bytes'        => 2048,
        'sha256'            => str_repeat('c', 64),
        'retention_until'   => now()->addYears(7)->toDateString(),
        'uploaded_by'       => User::where('email', 'admin@grc.local')->value('id'),
    ]);

    $this->get("/evidence/{$evidence->id}/edit")
        ->assertOk();
});

it('blocks deleting immutable evidence', function (): void {
    $evidence = EvidenceObject::create([
        'title'             => 'Dowód chroniony przed usunięciem',
        'classification'    => 'Restricted',
        'is_immutable'      => true,
        'original_filename' => 'protected.pdf',
        'storage_path'      => 'manual/protected-path',
        'mime_type'         => 'application/pdf',
        'size_bytes'        => 4096,
        'sha256'            => str_repeat('d', 64),
        'retention_until'   => now()->addYears(7)->toDateString(),
        'uploaded_by'       => User::where('email', 'admin@grc.local')->value('id'),
    ]);

    $this->delete("/evidence/{$evidence->id}")
        ->assertRedirect();

    // The record must still exist (soft-delete or hard-delete blocked)
    expect(EvidenceObject::withTrashed()->find($evidence->id))->not->toBeNull();
});

// ─────────────────────────────────────────────────────────────────────────────
// Scenarios: RBAC — ciso / admin access
// ─────────────────────────────────────────────────────────────────────────────

it('ciso can access scenario create page', function (): void {
    // beforeEach already sets ciso role
    $this->get('/scenarios/create')
        ->assertOk();
});

it('ciso can create a scenario template', function (): void {
    $countBefore = ScenarioTemplate::count();

    $response = $this->post('/scenarios', [
        'code'        => 'SCN-TEST-001',
        'name'        => 'Scenariusz testowy ransomware',
        'category_l1' => 'Cyber',
        'category_l2' => 'Ransomware',
        'description' => 'Opis scenariusza testowego.',
        'is_active'   => '1',
    ]);

    $response->assertRedirect();
    expect(ScenarioTemplate::count())->toBe($countBefore + 1);

    $scenario = ScenarioTemplate::latest()->first();
    expect($scenario->code)->toBe('SCN-TEST-001')
        ->and($scenario->category_l1)->toBe('Cyber');
});

it('risk_owner cannot access scenario create', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['risk_owner']);
    $this->actingAs($admin->fresh());

    $this->get('/scenarios/create')
        ->assertForbidden();
});

it('risk_owner cannot create scenario', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['risk_owner']);
    $this->actingAs($admin->fresh());

    $this->post('/scenarios', [
        'code'        => 'SCN-BLOCKED-001',
        'name'        => 'Scenariusz zablokowany',
        'category_l1' => 'Cyber',
        'category_l2' => 'DDoS',
    ])->assertForbidden();
});

it('ciso can edit scenario', function (): void {
    $scenario = ScenarioTemplate::create([
        'code'        => 'SCN-EDIT-001',
        'name'        => 'Scenariusz do edycji',
        'description' => 'Opis scenariusza do edycji.',
        'category_l1' => 'Operational',
        'category_l2' => 'Human Error',
        'is_active'   => true,
    ]);

    $this->get("/scenarios/{$scenario->id}/edit")
        ->assertOk();
});

it('admin can edit scenario', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['admin']);
    $this->actingAs($admin->fresh());

    $scenario = ScenarioTemplate::create([
        'code'        => 'SCN-ADMIN-001',
        'name'        => 'Scenariusz edycja admin',
        'description' => 'Opis scenariusza admin.',
        'category_l1' => 'Compliance',
        'category_l2' => 'GDPR',
        'is_active'   => true,
    ]);

    $this->get("/scenarios/{$scenario->id}/edit")
        ->assertOk();
});
