<?php

use App\Models\AppSetting;
use App\Models\Incident;
use App\Models\User;

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->assignRole('ciso');
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());
});

it('users without incident.view cannot see the security overview page', function (): void {
    $user = User::factory()->create();
    $user->two_factor_confirmed_at = now();
    $user->save();

    $this->actingAs($user)->get('/security-overview')->assertForbidden();
});

it('renders the security overview with per-connector counts and recent signals', function (): void {
    AppSetting::set('azure_identity_protection_enabled', '1');
    AppSetting::set('google_workspace_alerts_enabled', '0');

    Incident::create([
        'code' => 'INC-2026-90001',
        'title' => 'Impossible travel detected',
        'severity' => 'High',
        'status' => 'Investigating',
        'source' => 'Entra ID Identity Protection',
        'source_ref' => 'detection-abc',
        'occurred_at' => now(),
        'detected_at' => now(),
    ]);

    Incident::create([
        'code' => 'INC-2026-90002',
        'title' => 'Phishing email quarantined',
        'severity' => 'Medium',
        'status' => 'Closed',
        'source' => 'Google Workspace',
        'source_ref' => 'alert-xyz',
        'occurred_at' => now(),
        'detected_at' => now(),
    ]);

    $response = $this->get('/security-overview');

    $response->assertOk();
    $response->assertSee('Impossible travel detected');
    $response->assertSee('Phishing email quarantined');
    $response->assertSee('Entra ID');
    $response->assertSee('Google Workspace');

    $connectors = $response->viewData('connectors');
    expect($connectors['Entra ID Identity Protection']['open_count'])->toBe(1);
    expect($connectors['Entra ID Identity Protection']['enabled'])->toBeTrue();
    expect($connectors['Google Workspace']['open_count'])->toBe(0);
    expect($connectors['Google Workspace']['enabled'])->toBeFalse();
});

it('shows a never-synced state when a connector has no recorded sync timestamp', function (): void {
    $response = $this->get('/security-overview');

    $response->assertOk()->assertSee('nigdy');
});
