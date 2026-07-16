<?php

use App\Models\BcpPlan;
use App\Models\BcpTest;
use App\Models\CertificateInventory;
use App\Models\ComplianceException;
use App\Models\CryptoKey;
use App\Models\Training;
use App\Models\User;

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->assignRole('ciso');
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());
});

it('renders trainings index page', function (): void {
    $this->get('/trainings')
        ->assertOk()
        ->assertSee('Szkolenia');
});

it('creates a training', function (): void {
    $countBefore = Training::count();

    $response = $this->post('/trainings', [
        'title' => 'Szkolenie testowe bezpieczeństwa',
        'type' => 'security_awareness',
        'frequency' => 'annual',
        'expiry_days' => 365,
        'is_mandatory' => '1',
        'is_active' => '1',
    ]);

    $response->assertRedirect();

    expect(Training::count())->toBe($countBefore + 1);

    $training = Training::latest()->first();
    expect($training->code)->toStartWith('TRN-');
});

it('renders exceptions index page', function (): void {
    $this->get('/exceptions')
        ->assertOk()
        ->assertSee('Wyjątki');
});

it('creates a compliance exception and can approve it', function (): void {
    $countBefore = ComplianceException::count();

    $response = $this->post('/exceptions', [
        'title' => 'Wyjątek testowy',
        'exception_type' => 'control',
        'rationale' => 'Uzasadnienie testowe dla wyjątku compliance.',
    ]);

    $response->assertRedirect();
    expect(ComplianceException::count())->toBe($countBefore + 1);

    $exception = ComplianceException::latest()->first();
    expect($exception->status)->toBe('draft');
    expect($exception->code)->toStartWith('EXC-');

    // Submit for approval
    $this->post("/exceptions/{$exception->id}/submit")->assertRedirect();
    expect($exception->fresh()->status)->toBe('pending_approval');

    // Approve
    $this->post("/exceptions/{$exception->id}/approve")->assertRedirect();
    expect($exception->fresh()->status)->toBe('approved');
});

it('renders certificates index page', function (): void {
    $this->get('/certificates')
        ->assertOk()
        ->assertSee('Certyfikaty');
});

it('creates a certificate and daysUntilExpiry works', function (): void {
    $cert = CertificateInventory::create([
        'code' => 'CERT-TEST-001',
        'common_name' => 'test.example.com',
        'cert_type' => 'TLS',
        'environment' => 'production',
        'expires_at' => now()->addDays(45)->toDateString(),
        'status' => 'active',
        'renewal_days_before' => 30,
    ]);

    $days = $cert->daysUntilExpiry();
    expect($days)->toBeGreaterThanOrEqual(44);
    expect($days)->toBeLessThanOrEqual(46);
    expect($cert->urgencyLevel())->toBe('medium');
});

it('renders bcp index page', function (): void {
    $this->get('/bcp')
        ->assertOk()
        ->assertSee('BCP');
});

it('creates a bcp plan and records a test', function (): void {
    $countBefore = BcpPlan::count();
    $testCountBefore = BcpTest::count();

    $response = $this->post('/bcp', [
        'title' => 'Plan ciągłości działania IT',
        'plan_type' => 'bcp',
        'status' => 'draft',
        'version' => 1,
        'rto_hours' => 4,
        'rpo_minutes' => 60,
    ]);

    $response->assertRedirect();
    expect(BcpPlan::count())->toBe($countBefore + 1);

    $plan = BcpPlan::latest()->first();
    expect($plan->code)->toStartWith('BCP-');

    // Record a test
    $testResponse = $this->post("/bcp/{$plan->id}/test", [
        'test_type' => 'tabletop',
        'tested_at' => now()->toDateString(),
        'result' => 'pass',
        'gaps_identified' => null,
    ]);

    $testResponse->assertRedirect();
    expect(BcpTest::count())->toBeGreaterThan($testCountBefore);
});

it('renders org metrics page', function (): void {
    $this->get('/org-metrics')
        ->assertOk()
        ->assertSee('Wskaźniki organizacji');
});

it('CryptoKey sets next_rotation_due on save', function (): void {
    $key = CryptoKey::create([
        'code' => 'KEY-TEST-001',
        'name' => 'Test Encryption Key',
        'key_type' => 'AES',
        'storage_location' => 'KMS',
        'rotation_days' => 90,
        'last_rotated_at' => now()->toDateString(),
        'is_active' => true,
    ]);

    $expectedDate = now()->addDays(90)->toDateString();
    expect($key->next_rotation_due->toDateString())->toBe($expectedDate);
});
