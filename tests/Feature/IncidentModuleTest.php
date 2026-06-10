<?php

use App\Models\Incident;
use App\Models\User;

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->assignRole('ciso');
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());
});

it('renders incidents index page', function (): void {
    $this->get('/incidents')
        ->assertOk()
        ->assertSee('Incydenty');
});

it('creates an incident', function (): void {
    $countBefore = Incident::count();

    $response = $this->post('/incidents', [
        'title'       => 'Test incident bezpieczeństwa',
        'severity'    => 'Critical',
        'status'      => 'New',
        'source'      => 'Manual',
        'detected_at' => now()->toDateString(),
        'is_breach'   => false,
    ]);

    $response->assertRedirect();
    expect(Incident::count())->toBe($countBefore + 1);

    $incident = Incident::latest()->first();
    expect($incident->code)->toStartWith('INC-');
    expect($incident->title)->toBe('Test incident bezpieczeństwa');
    expect($incident->severity)->toBe('Critical');
    expect($incident->status)->toBe('New');
});

it('shows incident detail', function (): void {
    $incident = Incident::create([
        'code'     => 'INC-2025-00001',
        'title'    => 'Incydent testowy szczegółowy',
        'severity' => 'High',
        'status'   => 'New',
    ]);

    $this->get("/incidents/{$incident->id}")
        ->assertOk()
        ->assertSee('Incydent testowy szczegółowy');
});

it('updates incident status', function (): void {
    $incident = Incident::create([
        'code'     => 'INC-2025-00002',
        'title'    => 'Incydent do zmiany statusu',
        'severity' => 'Medium',
        'status'   => 'New',
    ]);

    $this->post("/incidents/{$incident->id}/status", [
        'status' => 'Investigating',
    ])->assertRedirect();

    expect($incident->fresh()->status)->toBe('Investigating');
    expect($incident->fresh()->acknowledged_at)->not->toBeNull();
});

it('toggles breach flag', function (): void {
    $incident = Incident::create([
        'code'      => 'INC-2025-00003',
        'title'     => 'Incydent do toggle breach',
        'severity'  => 'High',
        'status'    => 'New',
        'is_breach' => false,
    ]);

    $this->post("/incidents/{$incident->id}/breach")
        ->assertRedirect();

    expect($incident->fresh()->is_breach)->toBeTrue();

    // Toggle back
    $this->post("/incidents/{$incident->id}/breach")
        ->assertRedirect();

    expect($incident->fresh()->is_breach)->toBeFalse();
});

it('calculates ENISA score on save', function (): void {
    $incident = Incident::create([
        'code'                        => 'INC-2025-00004',
        'title'                       => 'Incydent z danymi ENISA',
        'severity'                    => 'Critical',
        'status'                      => 'New',
        'enisa_users_affected_band'   => 'lt100k',
        'enisa_service_impact'        => 'significant',
        'enisa_geographic_spread'     => 'national',
        'enisa_duration_hours'        => 8,
        'enisa_economic_impact'       => 'moderate',
    ]);

    expect($incident->enisa_severity_score)->not->toBeNull();
    expect((float) $incident->enisa_severity_score)->toBeGreaterThan(0);
    expect($incident->enisa_severity_level)->not->toBeNull();
});
