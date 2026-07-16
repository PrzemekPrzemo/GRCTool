<?php

use App\Models\Dpia;
use App\Models\DsarRequest;
use App\Models\GdprBreach;
use App\Models\ProcessingActivity;
use App\Models\ThirdParty;
use App\Models\User;

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->assignRole('ciso');
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());
});

// ─── RCP ────────────────────────────────────────────────────────────────────

it('renders RCP index page', function (): void {
    $this->get('/rcp')->assertOk()->assertSee('Rejestr Czynności Przetwarzania');
});

it('creates a processing activity and redirects to show', function (): void {
    $response = $this->post('/rcp', [
        'name' => 'Przetwarzanie danych pracowników',
        'legal_basis' => 'legal_obligation',
        'data_categories' => ['identification', 'employment'],
        'data_subjects' => ['employees'],
        'status' => 'active',
        'cross_border_transfer' => '0',
        'dpia_required' => '0',
    ]);

    $response->assertRedirect();
    $activity = ProcessingActivity::where('name', 'Przetwarzanie danych pracowników')->first();
    expect($activity)->not->toBeNull();
    expect($activity->code)->toStartWith('RCP-');
    expect($activity->legal_basis)->toBe('legal_obligation');
});

it('shows processing activity detail page', function (): void {
    $activity = ProcessingActivity::create([
        'code' => 'RCP-TEST-001',
        'name' => 'Test czynność',
        'status' => 'active',
        'legal_basis' => 'consent',
        'cross_border_transfer' => false,
        'dpia_required' => false,
    ]);

    $this->get("/rcp/{$activity->id}")->assertOk()->assertSee('Test czynność');
});

it('processing activity with special categories shows Art. 9 badge on index', function (): void {
    ProcessingActivity::create([
        'code' => 'RCP-TEST-002',
        'name' => 'Dane zdrowotne',
        'status' => 'active',
        'legal_basis' => 'legal_obligation',
        'special_categories' => ['health'],
        'cross_border_transfer' => false,
        'dpia_required' => true,
    ]);

    $this->get('/rcp')->assertOk()->assertSee('Art. 9');
});

it('links third party to processing activity via pivot', function (): void {
    $activity = ProcessingActivity::create([
        'code' => 'RCP-TEST-003',
        'name' => 'Czynność z podmiotem',
        'status' => 'active',
        'cross_border_transfer' => false,
        'dpia_required' => false,
    ]);

    $tp = ThirdParty::create([
        'code' => 'TP-TEST-0001',
        'name' => 'Dostawca testowy',
        'tier' => 'Medium',
        'is_active' => true,
    ]);

    $activity->thirdParties()->attach($tp->id, ['role' => 'processor']);

    expect($activity->thirdParties()->count())->toBe(1);
    expect($activity->thirdParties->first()->pivot->role)->toBe('processor');
});

// ─── GDPR Breach ────────────────────────────────────────────────────────────

it('renders GDPR breach index page', function (): void {
    $this->get('/gdpr-breaches')->assertOk()->assertSee('Naruszenia danych osobowych');
});

it('creates a GDPR breach and auto-calculates 72h UODO deadline', function (): void {
    $discoveredAt = now()->subHours(10);

    $response = $this->post('/gdpr-breaches', [
        'title' => 'Wyciek bazy klientów',
        'breach_type' => 'confidentiality',
        'discovered_at' => $discoveredAt->format('Y-m-d\TH:i'),
        'risk_level' => 'high',
        'notification_required' => '1',
        'data_subject_notification_required' => '0',
        'data_subjects_notified' => '0',
        'status' => 'open',
    ]);

    $response->assertRedirect();
    $breach = GdprBreach::where('title', 'Wyciek bazy klientów')->first();
    expect($breach)->not->toBeNull();
    expect($breach->code)->toStartWith('BREACH-');
    expect($breach->uodo_notification_deadline)->not->toBeNull();

    $expectedDeadline = $discoveredAt->copy()->addHours(72);
    expect($breach->uodo_notification_deadline->diffInMinutes($expectedDeadline))->toBeLessThan(2);
});

it('GdprBreach isOverdue() returns true when deadline passed and not yet reported', function (): void {
    $breach = GdprBreach::create([
        'code' => 'BREACH-TEST-001',
        'title' => 'Test breach',
        'notification_required' => true,
        'uodo_notification_deadline' => now()->subHours(5),
        'status' => 'open',
    ]);

    expect($breach->isOverdue())->toBeTrue();
});

it('GdprBreach isOverdue() returns false when reported', function (): void {
    $breach = GdprBreach::create([
        'code' => 'BREACH-TEST-002',
        'title' => 'Test breach reported',
        'notification_required' => true,
        'uodo_notification_deadline' => now()->subHours(5),
        'uodo_notified_at' => now()->subHours(3),
        'status' => 'reported',
    ]);

    expect($breach->isOverdue())->toBeFalse();
});

// ─── DPIA ────────────────────────────────────────────────────────────────────

it('renders DPIA index page', function (): void {
    $this->get('/dpias')->assertOk()->assertSee('DPIA');
});

it('creates a DPIA linked to a processing activity', function (): void {
    $activity = ProcessingActivity::create([
        'code' => 'RCP-TEST-DPIA',
        'name' => 'Analiza behawioralna',
        'status' => 'active',
        'dpia_required' => true,
        'cross_border_transfer' => false,
    ]);

    $response = $this->post('/dpias', [
        'title' => 'DPIA dla analizy behawioralnej',
        'processing_activity_id' => $activity->id,
        'assessment_date' => now()->format('Y-m-d'),
        'overall_risk_level' => 'high',
        'status' => 'draft',
        'dpo_consulted' => '0',
        'authority_consultation_required' => '0',
    ]);

    $response->assertRedirect();
    $dpia = Dpia::where('title', 'DPIA dla analizy behawioralnej')->first();
    expect($dpia)->not->toBeNull();
    expect($dpia->code)->toStartWith('DPIA-');
    expect($dpia->processing_activity_id)->toBe($activity->id);
    expect($dpia->overall_risk_level)->toBe('high');
});

it('approving a DPIA sets status to approved and records reviewer', function (): void {
    $dpia = Dpia::create([
        'code' => 'DPIA-TEST-001',
        'title' => 'Test DPIA',
        'status' => 'in_review',
        'dpo_consulted' => false,
        'authority_consultation_required' => false,
    ]);

    $this->post("/dpias/{$dpia->id}/approve")->assertRedirect();

    $dpia->refresh();
    expect($dpia->status)->toBe('approved');
    expect($dpia->reviewed_at)->not->toBeNull();
});

// ─── DSAR ────────────────────────────────────────────────────────────────────

it('renders DSAR index page', function (): void {
    $this->get('/dsar')->assertOk()->assertSee('Wnioski DSAR');
});

it('creates a DSAR request and auto-sets 30-day deadline', function (): void {
    $receivedAt = now();

    $response = $this->post('/dsar', [
        'request_type' => 'erasure',
        'requester_name' => 'Jan Kowalski',
        'requester_email' => 'jan@example.com',
        'request_description' => 'Proszę o usunięcie wszystkich moich danych osobowych.',
        'received_at' => $receivedAt->format('Y-m-d\TH:i'),
        'status' => 'pending',
        'identity_verified' => '0',
    ]);

    $response->assertRedirect();
    $dsar = DsarRequest::where('requester_name', 'Jan Kowalski')->first();
    expect($dsar)->not->toBeNull();
    expect($dsar->code)->toStartWith('DSAR-');
    expect($dsar->deadline_at)->not->toBeNull();
    expect($dsar->deadline_at->format('Y-m-d'))->toBe($receivedAt->copy()->addDays(30)->format('Y-m-d'));
});

it('DsarRequest isOverdue() returns true for pending request past deadline', function (): void {
    $dsar = DsarRequest::create([
        'code' => 'DSAR-TEST-001',
        'request_type' => 'access',
        'requester_name' => 'Test User',
        'request_description' => 'Test',
        'received_at' => now()->subDays(35),
        'deadline_at' => now()->subDays(5),
        'status' => 'pending',
    ]);

    expect($dsar->isOverdue())->toBeTrue();
});

it('DsarRequest deadline extension sets 90-day deadline', function (): void {
    $receivedAt = now()->subDays(25);
    $dsar = DsarRequest::create([
        'code' => 'DSAR-TEST-002',
        'request_type' => 'access',
        'requester_name' => 'Test User',
        'request_description' => 'Test',
        'received_at' => $receivedAt,
        'deadline_at' => $receivedAt->copy()->addDays(30),
        'status' => 'in_progress',
    ]);

    $this->post("/dsar/{$dsar->id}/extend", [
        'extension_reason' => 'Złożoność wniosku wymaga dodatkowego czasu.',
    ])->assertRedirect();

    $dsar->refresh();
    expect($dsar->deadline_extended)->toBeTrue();
    expect($dsar->extended_deadline_at)->not->toBeNull();
    expect($dsar->extended_deadline_at->format('Y-m-d'))->toBe($receivedAt->copy()->addDays(90)->format('Y-m-d'));
});

// ─── ThirdParty ───────────────────────────────────────────────────────────────

it('renders third parties index page', function (): void {
    $this->get('/third-parties')->assertOk()->assertSee('Strony trzecie');
});

it('creates a third party and redirects to show', function (): void {
    $response = $this->post('/third-parties', [
        'name' => 'Cloudflare Inc.',
        'service_provided' => 'CDN i ochrona DDoS',
        'country_of_processing' => 'US',
        'transfer_mechanism' => 'SCC',
        'tier' => 'High',
        'is_active' => '1',
    ]);

    $response->assertRedirect();
    $tp = ThirdParty::where('name', 'Cloudflare Inc.')->first();
    expect($tp)->not->toBeNull();
    expect($tp->code)->toStartWith('TP-');
    expect($tp->tier)->toBe('High');
});

// ─── Policy ───────────────────────────────────────────────────────────────────

it('renders policies index page', function (): void {
    $this->get('/policies')->assertOk()->assertSee('Polityki');
});

it('renders all new RODO index pages without errors', function (): void {
    $pages = ['/rcp', '/gdpr-breaches', '/dpias', '/dsar', '/third-parties', '/policies'];
    foreach ($pages as $page) {
        $this->get($page)->assertOk();
    }
});
