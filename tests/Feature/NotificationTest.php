<?php

use App\Models\CertificateInventory;
use App\Models\DsarRequest;
use App\Models\Incident;
use App\Models\User;
use App\Models\Vulnerability;
use App\Notifications\GrcAlertNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->assignRole('ciso');
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());
});

// ─────────────────────────────────────────────────────────────────────────────
// SEKCJA A: Slack Webhook Tests
// ─────────────────────────────────────────────────────────────────────────────

it('sends slack notification when critical incident is created', function (): void {
    Http::fake(['*' => Http::response('ok', 200)]);
    config(['slack.enabled' => true, 'slack.webhook_url' => 'https://hooks.slack.com/services/test']);

    $this->post('/incidents', [
        'title'       => 'Krytyczny incydent testowy',
        'severity'    => 'Critical',
        'status'      => 'New',
        'source'      => 'Manual',
        'detected_at' => now()->toDateString(),
        'is_breach'   => false,
    ])->assertRedirect();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'hooks.slack.com'));
});

it('sends slack notification when breach flag is toggled on', function (): void {
    // Create incident with is_breach = false so toggle will set it to true
    $incident = Incident::create([
        'code'      => 'INC-2025-BREACH-01',
        'title'     => 'Incydent do oznaczenia jako breach',
        'severity'  => 'High',
        'status'    => 'New',
        'is_breach' => false,
    ]);

    Http::fake(['*' => Http::response('ok', 200)]);
    config(['slack.enabled' => true, 'slack.webhook_url' => 'https://hooks.slack.com/services/test']);

    $this->post("/incidents/{$incident->id}/breach")->assertRedirect();

    // After toggle: is_breach is now true → Slack should have been called
    expect($incident->fresh()->is_breach)->toBeTrue();
    Http::assertSent(fn ($request) => str_contains($request->url(), 'hooks.slack.com'));
});

it('sends slack notification for critical vulnerability', function (): void {
    Http::fake(['*' => Http::response('ok', 200)]);
    config(['slack.enabled' => true, 'slack.webhook_url' => 'https://hooks.slack.com/services/test']);

    $this->post('/vulnerabilities', [
        'title'          => 'Krytyczna podatność testowa',
        'severity'       => 'Critical',
        'source'         => 'Manual',
        'source_type'    => 'Manual',
        'discovered_at'  => now()->toDateString(),
    ])->assertRedirect();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'hooks.slack.com'));
});

it('sends slack notification for major finding', function (): void {
    Http::fake(['*' => Http::response('ok', 200)]);
    config(['slack.enabled' => true, 'slack.webhook_url' => 'https://hooks.slack.com/services/test']);

    $admin = User::where('email', 'admin@grc.local')->firstOrFail();

    $this->post('/findings', [
        'title'         => 'Poważny finding testowy',
        'description'   => 'Opis poważnego findingu testowego.',
        'source'        => 'Internal Audit',
        'severity'      => 'Major',
        'owner_id'      => $admin->id,
        'discovered_at' => now()->toDateString(),
    ])->assertRedirect();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'hooks.slack.com'));
});

it('does not send slack when disabled', function (): void {
    Http::fake(['*' => Http::response('ok', 200)]);
    config(['slack.enabled' => false, 'slack.webhook_url' => 'https://hooks.slack.com/services/test']);

    $this->post('/incidents', [
        'title'       => 'Incydent bez Slacka',
        'severity'    => 'Critical',
        'status'      => 'New',
        'source'      => 'Manual',
        'detected_at' => now()->toDateString(),
        'is_breach'   => false,
    ])->assertRedirect();

    Http::assertNothingSent();
});

// ─────────────────────────────────────────────────────────────────────────────
// SEKCJA B: Email Alert Command Tests
// ─────────────────────────────────────────────────────────────────────────────

it('sends alert for expiring certificate', function (): void {
    Notification::fake();

    // Create active cert expiring in 15 days (within the 30-day window)
    CertificateInventory::create([
        'code'                => 'CERT-TEST-ALERT-001',
        'common_name'         => 'alert.example.com',
        'cert_type'           => 'TLS',
        'environment'         => 'production',
        'expires_at'          => now()->addDays(15)->toDateString(),
        'status'              => 'active',
        'renewal_days_before' => 30,
    ]);

    $this->artisan('grc:send-alerts')->assertExitCode(0);

    Notification::assertSentOnDemand(GrcAlertNotification::class);
});

it('sends alert for overdue DSAR request', function (): void {
    Notification::fake();

    // Create a DSAR that is 27 days old and still pending.
    // We must backdate created_at because the command checks created_at <= now()->subDays(25).
    $dsar = DsarRequest::create([
        'code'                => 'DSAR-TEST-ALERT-001',
        'request_type'        => 'access',
        'requester_name'      => 'Jan Kowalski',
        'requester_email'     => 'jan.kowalski@example.com',
        'request_description' => 'Proszę o dostęp do moich danych.',
        'received_at'         => now()->subDays(27),
        'status'              => 'pending',
    ]);

    // Force-backdate created_at so the alert condition is satisfied
    $dsar->timestamps = false;
    $dsar->created_at = now()->subDays(27);
    $dsar->save();
    $dsar->timestamps = true;

    $this->artisan('grc:send-alerts')->assertExitCode(0);

    Notification::assertSentOnDemand(GrcAlertNotification::class);
});

it('does not send alert for already closed DSAR', function (): void {
    Notification::fake();

    // Create a DSAR that is 27 days old but already completed
    DsarRequest::create([
        'code'                => 'DSAR-TEST-CLOSED-001',
        'request_type'        => 'erasure',
        'requester_name'      => 'Anna Nowak',
        'requester_email'     => 'anna.nowak@example.com',
        'request_description' => 'Proszę o usunięcie moich danych.',
        'received_at'         => now()->subDays(27),
        'status'              => 'completed',
    ]);

    $this->artisan('grc:send-alerts')->assertExitCode(0);

    Notification::assertNothingSent();
});

it('command runs successfully', function (): void {
    $this->artisan('grc:send-alerts')->assertExitCode(0);
});
